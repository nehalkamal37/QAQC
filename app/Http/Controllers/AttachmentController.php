<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attachment;
use Illuminate\Support\Facades\Storage;
use App\Models\QAItem;
use App\Models\Sheet;
class AttachmentController extends Controller
{
  
public function store(Request $request, $itemId)
{
    $item = QAItem::findOrFail($itemId);

    foreach ($request->file('files') as $file) {

        $path = $file->store('attachments', 'public');

        $att = $item->attachments()->create([
            'filename' => $file->getClientOriginalName(),
            'path'     => $path,
            'mime'     => $file->getClientMimeType(),
            'user_id'  => auth()->id()
        ]);

        // 🔥 LOG EVENT
    
logActivity([
    'project_id' => $item->sheet->phase->project_id,
    'phase_id'   => $item->sheet->phase_id,
    'sheet_id'   => $item->sheet_id,
    'qa_item_id' => $item->id,
    'action_type' => 'attachment_uploaded',
    'note'        => $file->getClientOriginalName(),
    'new'         => [
        'filename' => $file->getClientOriginalName(),
        'path'     => $path,
        'mime'     => $file->getMimeType()
    ]
]);
}
    return back()->with('success', 'Files uploaded.');
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


public function storeForSheet(Request $request, $sheetId)
{
    $sheet = Sheet::findOrFail($sheetId);

    $request->validate([
        'files.*' => 'required|mimes:jpg,jpeg,png,pdf|max:20480'
    ]);

    foreach ($request->file('files') as $file) {

        $path = $file->store('attachments', 'public');

        $sheet->attachments()->create([
            'user_id'  => auth()->id(),
            'filename' => $file->getClientOriginalName(),
            'path'     => $path,
            'mime'     => $file->getMimeType(),
        ]);
    }

    return back()->with('success', 'Files uploaded to sheet!');
}



}
