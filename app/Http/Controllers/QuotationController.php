<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Prescription;
use App\Models\PrescriptionImage;
use App\Models\Quotation;
use App\Models\User;
use App\Models\QuotationItem;

use App\Mail\NotifyQuotationmail;
use App\Notifications\NotifyQuotationStatus;
use Illuminate\Support\Facades\Mail;

class QuotationController extends Controller
{
    //Show the form for creating a new quotation based on a prescription.
    public function create($id)
    {
        $prescriptionImages = PrescriptionImage::where('prescription_id',$id)->get();
        return view('pharmacy-user.create-quotation',compact('prescriptionImages','id'));
    }

    public function show(){
        $user = Auth::user();
        $quotations = Quotation::with('user');

        if ($user->type == 'user') {
            // If the user is a regular user, filter the quotations by user ID.
            $quotations = $quotations->where('user_id', $user->id);
        }
        $quotations = $quotations->get();
        return view('view-quotations', compact('quotations'));
    }

    public function findItems($id){
        $quotationItems = QuotationItem::where('quotation_id',$id)->get();
        return response()->json($quotationItems);
    }

    public function updateStatus(Request $request){
        $quotation = Quotation::find($request->id);
        $quotation->status = $request->status;
        $quotation->save();

        if (Auth::user()->type == 'user') {
            // If the logged-in user is a regular user, notify all pharmacy users about the quotation status change.
            $pharmacyUsers = User::where('type', 'pharmacy')->get();
            foreach ($pharmacyUsers as $pharmacyUser) {
                $pharmacyUser->notify(new NotifyQuotationStatus($quotation, Auth::user()));
            }
        } else {
            // If the logged-in user is a pharmacy user, notify the regular user about the quotation status change.
            $user = User::find($quotation->user_id);
            $user->notify(new NotifyQuotationStatus($quotation, $user));
        }

        return response()->json("Updated");
    }

    public function store(Request $request, $id){
        $prescription = Prescription::find($id);
        $userId= $prescription->user_id;
        
        // Create a new Quotation object and fill its fields with data from the request.
        $quotation = new Quotation;
        $quotation->total = $request->total;
        $quotation->user_id = $userId;
        $quotation->prescription_id = $id;
        $quotation->save();

        // Loop through each item in the cart data from the request, and create a new QuotationItem object for each one.
        foreach($request->cartData as $item){
            $quotationItem = new QuotationItem;
            $quotationItem->drug = $item['name'];
            $quotationItem->amount = $item['price'];
            $quotationItem->quantity = $item['quantity'];
            $quotationItem->quotation_id = $quotation->id;
            $quotationItem->save();
        }

        // Get the User object associated with the prescription owner, and send them a notification and email.
        $user = User::find($userId);
        $quotation->status = "created";
        $user->notify(new NotifyQuotationStatus($quotation, $user));
        Mail::to($user->email)->send(new NotifyQuotationmail($user, $request->total));

        return response()->json(["message"=>"Quotation created", "data"=>$quotation]);
    }
}
