<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Prescription;
use App\Models\PrescriptionImage;
use Illuminate\Support\Facades\Auth;

use Illuminate\Validation\Rule;
use Carbon\Carbon;

class PrescriptionController extends Controller
{
    //Show the form for creating a new prescription.
    public function create()
    {
        return view('user.create-prescription');
    }

public function store(Request $request)
    {
        $rules = [
            'address' => 'required|string|max:255',
            'delivery_time' => [
                'required',
                'after: '.Carbon::now()->addHours(2) // delivery_time must be at least 2 hours from now
            ],
            'note' => 'nullable|string|max:500',
            'images.*' => 'image|max:2048', // max file size of 2 MB per image
            'images' => 'required|array|min:1|max:5', // at most 5 images allowed
        ];
        $messages = [
            'address.required' => 'The delivery address field is required.',
            'address.max' => 'The delivery address may not be greater than 255 characters.',
            'delivery_time.required' => 'The delivery time field is required.',
            'delivery_time.after' => 'The delivery time must be at least 2 hours from now.',
            'note.max' => 'The note may not be greater than 500 characters.',
            'images.*.image' => 'The uploaded file must be an image.',
            'images.required' => 'At least one prescription image is required.',
            'images.*.max' => 'The uploaded file may not be greater than 2 MB.',
            'images.min' => 'At least one prescription image is required.',
            'images.max' => 'At most 5 images are allowed.',
            'images.array' => 'The uploaded files must be an array of images.',
        ];
        $validatedData = $request->validate($rules, $messages);
        // get the authenticated user
        $user = auth()->user();

        $prescription = Prescription::create([
            'user_id' => auth()->user()->id,
            'note' => $request->input('note'),
            'address' => $request->input('address'),
            'delivery_time' => $request->input('delivery_time'),
        ]);

        //Multiple Image uploads
        $images = $request->file('images');
        foreach ($images as $key => $image) {

            // Generate a unique filename for the image
            $filename = time() . '_' . $key . '.' . $image->getClientOriginalExtension();
            
            // Store the image in the "storage/app/public/prescriptions" directory, 
            // Linked with public folder as storage/prescriptions
            $path = $image->storeAs('public/prescriptions', $filename);

            $prescriptionImage = new PrescriptionImage;
            $prescriptionImage->prescription_id = $prescription->id;
            $prescriptionImage->image_url = Storage::url($path);
            $prescriptionImage->save();
        }

        return redirect()->route('prescriptions.index')->with('success', 'Prescription created successfully.');
    }

    public function show(){
        $user = Auth::user();
        $prescriptions = Prescription::with('user');

        //If the authenticated user is a regular user, filter the prescriptions query to only include prescriptions created by that user
        if($user->type == 'user') {
            $prescriptions = $prescriptions->where('user_id', $user->id);
        }
        $prescriptions = $prescriptions->get();

        return view('view-prescriptions', compact('prescriptions'));
    }
}