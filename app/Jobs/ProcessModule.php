<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\ModuleUploadErrors;

use App\Models\Module;

class ProcessModule implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // $path = $file->storeAs('public/storage', $filename);
        // $file = Storage::get($path);

        $file = request()->file('csv_file');

        $filename = $file->getClientOriginalName();

        $file->move("storage", $filename);
        $filepath = public_path("storage/".$filename);
        $file = fopen($filepath, "r");

        $more_col = [];

        $total_cols = config('custom.column_counts');
        $col_name = config('custom.column_names');
        $i = 0;
        while ($filedata = fgetcsv($file)) {
            $col_num = count($filedata);

            if ($col_num > $total_cols) {
                $more_col = [...$more_col, $i];
            }

            if ($col_num < $total_cols) {
                // dd($filedata);
                continue;
            }

            for ($c = 0; $c < $total_cols; $c++) {
                $importData_arr[$i][] = $filedata[$c];
            }

            $i++;

            // if ($i === 30) break;
        }

        $null_cells = [];
        $mismatch_cells = [];

        // $null_rules = [
        //     '0' => ['required'],
        //     '1' => ['required'],
        //     '2' => ['required'],
        // ];

        // $mismatch_rules = [
        //     '0' => ['regex:/^[a-z0-9 ]+$/i'],
        //     '1' => ['regex:/^[a-z0-9 ]+$/i'],
        //     '2' => ['regex:/^[a-z0-9 ]+$/i'],
        // ];

        $null_rules = [];
        $mismatch_rules = [];

        for ($i = 0; $i < $total_cols; $i++) {
            $null_rules[$i] = ['required'];
            $mismatch_rules[$i] = ['regex:/^'.config('custom.row_validation_condition').'+$/i'];
        }

        foreach ($importData_arr as $key => $importData) {
            $error_flag = false;

            $null_validator = Validator::make($importData, $null_rules, [
                'required' => ':attribute',
            ]);

            if ($null_validator->fails()) {
                foreach ($null_validator->errors()->all() as $message) {
                    $null_cells[$message] = $null_cells[$message] ?? [];
                    $null_cells[$message] = [...$null_cells[$message], $key];
                }
                
                $error_flag = true;
            }

            $pattern_validator = Validator::make($importData, $mismatch_rules, [
                'regex' => ':attribute',
            ]);

            if ($pattern_validator->fails()) {
                foreach ($pattern_validator->errors()->all() as $message) {
                    $mismatch_cells[$message] = $mismatch_cells[$message] ?? [];
                    $mismatch_cells[$message] = [...$mismatch_cells[$message], $key];
                }
            
                $error_flag = true;
            }

            if (! $error_flag) {
                // $table_data = [
                //     'module_code' => $importData[0],
                //     'module_name' => $importData[1],
                //     'module_term' => $importData[2],
                // ];

                $table_data = [];

                for ($i = 0; $i < $total_cols; $i++) { 
                    $table_data = array_merge($table_data, [$col_name[$i] => $importData[$i]]);
                }

                $module = Module::create($table_data);
            }
        }

        $errors = [];

        foreach ($null_cells as $col => $rows) {
            $msg = $col_name[$col] . ' is missing at row ';

            foreach ($rows as $row) {
                $msg = $msg . $row . ', ';
            }

            $msg = Str::replaceLast(', ', '.', $msg);

            $msg = Str::replaceLast(', ', ' and ', $msg);

            $errors = [...$errors, $msg];
        }

        foreach ($mismatch_cells as $col => $rows) {
            $msg = $col_name[$col] . ' contains symbols at row ';

            foreach ($rows as $row) {
                $msg = $msg . $row . ', ';
            }

            $msg = Str::replaceLast(', ', '.', $msg);

            $msg = Str::replaceLast(', ', ' and ', $msg);

            $errors = [...$errors, $msg];
        }

        fclose($file);

        Mail::to('charush@accubits.com')->send(new ModuleUploadErrors($errors));
    }
}
