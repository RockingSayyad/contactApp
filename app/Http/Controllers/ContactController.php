<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;
use Illuminate\Support\Facades\DB;
use SimpleXMLElement;
use Illuminate\Support\Facades\Log;


class ContactController extends Controller
{
    // Display all contacts
    // public function index()
    // {
    //     $contacts = Contact::all();
    //     return view('contacts.index', compact('contacts'));
    // }
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Get the pagination parameters from DataTables
            $perPage = $request->get('length', 10);
            $start = $request->get('start', 0);

            // Fetch the paginated data
            $contacts = Contact::select('id', 'name', 'phone')
                ->skip($start)
                ->take($perPage)
                ->get();

            // Get the total number of records (without pagination)
            $totalContacts = Contact::count();

            return response()->json([
                'draw' => $request->get('draw'),
                'recordsTotal' => $totalContacts,
                'recordsFiltered' => $totalContacts,
                'data' => $contacts,
            ]);
        }

        return view('contacts.index');
    }


    // Store a new contact
    // public function store(Request $request)
    // {
    //     $validated = $request->validate([
    //         'name' => 'required|string|max:255',
    //         'phone' => 'required|string|max:15',
    //     ]);

    //     Contact::create($validated);
    //     return redirect()->route('contacts.index');
    // }
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'phone' => 'required|string',
        ]);

        Contact::updateOrCreate(['id' => $request->id], $data);
        return response()->json(['success' => true]);
    }

    public function show($id)
    {
        $contact = Contact::find($id);
        return response()->json($contact);
    }
    public function import(Request $request)
    {


        Log::error('Request Details: ' . json_encode([
            'input' => $request->all(),
            'headers' => $request->headers->all(),
            'url' => $request->url(),
            'method' => $request->method(),
        ]));
        // $request->validate([
        //     'xml_file' => 'required|file|mimes:xml|max:10240',
        // ]);

        $xmlContent = file_get_contents($request->file('xml_file'));
        $xml = new SimpleXMLElement($xmlContent);

        $contacts = [];

        foreach ($xml->contact as $contact) {
            $contacts[] = [
                'name' => (string) $contact->name,
                'phone' => (string) $contact->phone,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        Contact::insert($contacts);

        return redirect()->route('contacts.index')->with('success', 'Contacts imported successfully!');
    }

    public function destroy($id)
    {
        $contact = Contact::find($id); // Use find instead of findOrFail
        if (!$contact) {
            return response()->json(['error' => 'Contact not found.'], 404);
        }

        $contact->delete();
        return response()->json(['success' => true]);
    }
}
