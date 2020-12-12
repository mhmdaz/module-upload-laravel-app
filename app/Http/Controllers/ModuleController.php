<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

use App\Jobs\ProcessModule;

class ModuleController extends Controller
{
    public function uploadForm()
    {
    	return view('upload');
    }

    public function upload()
    {
    	if (request()->hasFile('csv_file')) {
	    	$file = request()->file('csv_file');

	    	$extension = $file->getClientOriginalExtension();

	    	$valid_extension = array("csv");

	    	if (in_array(strtolower($extension), $valid_extension)) {
	    		// $processModule = new ProcessModule();

		        $file = request()->file('csv_file');

		        $filename = $file->getClientOriginalName();

		        $file->move("storage", $filename);

		        processModule::dispatch($filename);

	    		// $this->dispatch($processModule($filename));
		    	
		    	$status = 'Processing...';
	    	}
	    	else {
	    		// Session::flash('message','file is not csv.');
		    	$status = 'file is not csv';
	    	}
	    }
	    else {
		    // Session::flash('message','no file selected.');
		    $status = 'no file selected';
	    }

	    return back()->with('message', $status);
    }
}
