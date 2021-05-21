<?php

namespace App\Http\Controllers;

use App\Models\Inquiry;
use App\Models\InquiryAnswer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Inquiries\Answers\StoreRequest;
use App\Http\Requests\Inquiries\Answers\ShowRequest;
use App\Http\Requests\Inquiries\Answers\UpdateRequest;
use App\Http\Requests\Inquiries\Answers\DestroyRequest;


class InquiryAnswerController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return InquiryAnswer
     */
    public function store(StoreRequest $request, int $inquiryId)
    {
        // store
        $answer = new InquiryAnswer();
        $answer->user_id = Auth::id();
        $answer->inquiry_id = $inquiryId;
        $answer->answer = $request->get('answer');
        $answer->save();

        return InquiryAnswer::find($answer->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function show(ShowRequest $request, int $id)
    {
        return InquiryAnswer::with('inquiry')->findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     * !Warning! The router for this method is customized.
     *
     * @param UpdateRequest $request
     * @param  int $id
     * @return Response
     */
    public function update(UpdateRequest $request, int $inquiryId)
    {
        // getting original data
        $id = Inquiry::findOrFail($inquiryId)->answer->id;
        $answer = InquiryAnswer::findOrFail($id);

        // update
        $answer->answer = $request->get('answer') ?? $answer->answer;
        $answer->saveOrFail();

        // response
        return InquiryAnswer::find($id);
    }

    /**
     * Remove the specified resource from storage.
     * !Warning! The router for this method is customized.
     *
     * @param  DestroyRequest $request
     * @param  int $inquiryId
     * @return Response
     */
    public function destroy(DestroyRequest $request, int $inquiryId): Response
    {
        $answer = Inquiry::findOrFail($inquiryId)->answer;
        InquiryAnswer::findOrFail(@$answer->id)->destroy();

        return response()->noContent();
    }
}
