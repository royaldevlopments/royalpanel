<?php

namespace RoyalPanel\Http\Controllers\Admin\Royal;

use RoyalPanel\Http\Controllers\Controller;
use RoyalPanel\Models\EmailTemplate;
use RoyalPanel\Http\Requests\Admin\Royal\RoyalEmailTemplateRequest;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class RoyalEmailTemplateController extends Controller
{
    public function index(): View
    {
        $templates = EmailTemplate::orderBy('template_key')->get();
        return view('admin.royal.email-templates.index', [
                'templates' => $templates,
                'navbar' => 'email-templates',
            ]
        );
    }

    public function edit(EmailTemplate $template): View
    {
        return view('admin.royal.email-templates.edit', [
                'template' => $template,
                'navbar' => 'email-templates',
            ]
        );
    }

    public function update(RoyalEmailTemplateRequest $request, EmailTemplate $template): RedirectResponse
    {
        $template->update($request->validated());

        return redirect()->route('admin.royal.email-templates')
            ->with('success', "Template '{$template->template_key}' updated successfully.");
    }
}
