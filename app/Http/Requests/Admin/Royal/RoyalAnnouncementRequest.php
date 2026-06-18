<?php

namespace RoyalPanel\Http\Requests\Admin\Royal;

use RoyalPanel\Http\Requests\Admin\AdminFormRequest;

class RoyalAnnouncementRequest extends AdminFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'royal:announcement' => 'required|in:false,true',
            'royal:announcementColor' => 'required|string',
            'royal:announcementIcon' => 'required|string',
            'royal:announcementMessage' => 'nullable|string',
            'royal:announcementCta' => 'required|in:false,true',
            'royal:announcementCtaTitle' => 'required|string',
            'royal:announcementCtaLink' => 'required|string',
            'royal:announcementDismissable' => 'required|in:false,true',
        ];
    }
}