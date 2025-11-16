<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attachment;
use Illuminate\Support\Facades\Storage;


class AttachmentController extends Controller
{
  
    public function store(Request $request, $itemId)
{
    $request->validate([
        'files.*' => 'required|mimes:jpg,jpeg,png,pdf|max:20480'
    ]);

    foreach ($request->file('files') as $file) {

       // $path = $file->store('qa_attachments', 'public');
$path = $file->store('attachments', 'public');

        Attachment::create([
            'qa_item_id' => $itemId,
            'user_id'    => auth()->id(),
            'filename'   => $file->getClientOriginalName(),
            'path'       => $path,
            'mime'       => $file->getMimeType(),
        ]);
    }

    return back()->with('success', 'Files uploaded successfully');
}


    public function destroy($id)
    {
        $attachment = Attachment::findOrFail($id);

        if (!auth()->user()->hasRole(['Admin','PM','Senior Reviewer','Reviewer']) &&
            auth()->id() !== $attachment->user_id) {
            abort(403, 'Unauthorized');
        }

        Storage::disk('public')->delete($attachment->path);
        $attachment->delete();

        return back()->with('success', 'Attachment deleted');
    }


    public function addNote(Request $request, $attId) {
    $att = Attachment::findOrFail($attId);
    $att->note = $request->note;
    $att->save();

    return back()->with('success', 'Note added');
}

}
