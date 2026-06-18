<?php

namespace RoyalPanel\Http\Controllers\Admin\Nests;

use RoyalPanel\Models\Egg;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use RoyalPanel\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;
use RoyalPanel\Services\Eggs\Sharing\EggExporterService;
use RoyalPanel\Services\Eggs\Sharing\EggImporterService;
use RoyalPanel\Http\Requests\Admin\Egg\EggImportFormRequest;
use RoyalPanel\Services\Eggs\Sharing\EggUpdateImporterService;

class EggShareController extends Controller
{
    /**
     * EggShareController constructor.
     */
    public function __construct(
        protected AlertsMessageBag $alert,
        protected EggExporterService $exporterService,
        protected EggImporterService $importerService,
        protected EggUpdateImporterService $updateImporterService,
    ) {
    }

    /**
     * @throws \RoyalPanel\Exceptions\Repository\RecordNotFoundException
     */
    public function export(Egg $egg): Response
    {
        $filename = trim(preg_replace('/\W/', '-', kebab_case($egg->name)), '-');

        return response($this->exporterService->handle($egg->id), 200, [
            'Content-Transfer-Encoding' => 'binary',
            'Content-Description' => 'File Transfer',
            'Content-Disposition' => 'attachment; filename=egg-' . $filename . '.json',
            'Content-Type' => 'application/json',
        ]);
    }

    /**
     * Import a new service option using an XML file.
     *
     * @throws \RoyalPanel\Exceptions\Model\DataValidationException
     * @throws \RoyalPanel\Exceptions\Repository\RecordNotFoundException
     * @throws \RoyalPanel\Exceptions\Service\Egg\BadJsonFormatException
     * @throws \RoyalPanel\Exceptions\Service\InvalidFileUploadException
     */
    public function import(EggImportFormRequest $request): RedirectResponse
    {
        $egg = $this->importerService->handle($request->file('import_file'), $request->input('import_to_nest'));
        $this->alert->success(trans('admin/nests.eggs.notices.imported'))->flash();

        return redirect()->route('admin.nests.egg.view', ['egg' => $egg->id]);
    }

    /**
     * Update an existing Egg using a new imported file.
     *
     * @throws \RoyalPanel\Exceptions\Model\DataValidationException
     * @throws \RoyalPanel\Exceptions\Repository\RecordNotFoundException
     * @throws \RoyalPanel\Exceptions\Service\Egg\BadJsonFormatException
     * @throws \RoyalPanel\Exceptions\Service\InvalidFileUploadException
     */
    public function update(EggImportFormRequest $request, Egg $egg): RedirectResponse
    {
        $this->updateImporterService->handle($egg, $request->file('import_file'));
        $this->alert->success(trans('admin/nests.eggs.notices.updated_via_import'))->flash();

        return redirect()->route('admin.nests.egg.view', ['egg' => $egg]);
    }
}
