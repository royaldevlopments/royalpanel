<?php

namespace RoyalPanel\Http\Controllers\Admin\Nests;

use Illuminate\View\View;
use RoyalPanel\Models\Egg;
use RoyalPanel\Models\EggVariable;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Illuminate\View\Factory as ViewFactory;
use RoyalPanel\Http\Controllers\Controller;
use RoyalPanel\Contracts\Repository\EggRepositoryInterface;
use RoyalPanel\Services\Eggs\Variables\VariableUpdateService;
use RoyalPanel\Http\Requests\Admin\Egg\EggVariableFormRequest;
use RoyalPanel\Services\Eggs\Variables\VariableCreationService;
use RoyalPanel\Contracts\Repository\EggVariableRepositoryInterface;

class EggVariableController extends Controller
{
    /**
     * EggVariableController constructor.
     */
    public function __construct(
        protected AlertsMessageBag $alert,
        protected VariableCreationService $creationService,
        protected VariableUpdateService $updateService,
        protected EggRepositoryInterface $repository,
        protected EggVariableRepositoryInterface $variableRepository,
        protected ViewFactory $view,
    ) {
    }

    /**
     * Handle request to view the variables attached to an Egg.
     *
     * @throws \RoyalPanel\Exceptions\Repository\RecordNotFoundException
     */
    public function view(int $egg): View
    {
        $egg = $this->repository->getWithVariables($egg);

        return view('admin.eggs.variables', ['egg' => $egg]);
    }

    /**
     * Handle a request to create a new Egg variable.
     *
     * @throws \RoyalPanel\Exceptions\Model\DataValidationException
     * @throws \RoyalPanel\Exceptions\Service\Egg\Variable\BadValidationRuleException
     * @throws \RoyalPanel\Exceptions\Service\Egg\Variable\ReservedVariableNameException
     */
    public function store(EggVariableFormRequest $request, Egg $egg): RedirectResponse
    {
        $this->creationService->handle($egg->id, $request->normalize());
        $this->alert->success(trans('admin/nests.variables.notices.variable_created'))->flash();

        return redirect()->route('admin.nests.egg.variables', $egg->id);
    }

    /**
     * Handle a request to update an existing Egg variable.
     *
     * @throws \RoyalPanel\Exceptions\DisplayException
     * @throws \RoyalPanel\Exceptions\Model\DataValidationException
     * @throws \RoyalPanel\Exceptions\Repository\RecordNotFoundException
     * @throws \RoyalPanel\Exceptions\Service\Egg\Variable\ReservedVariableNameException
     */
    public function update(EggVariableFormRequest $request, Egg $egg, EggVariable $variable): RedirectResponse
    {
        $this->updateService->handle($variable, $request->normalize());
        $this->alert->success(trans('admin/nests.variables.notices.variable_updated', [
            'variable' => htmlspecialchars($variable->name),
        ]))->flash();

        return redirect()->route('admin.nests.egg.variables', $egg->id);
    }

    /**
     * Handle a request to delete an existing Egg variable from the Panel.
     */
    public function destroy(int $egg, EggVariable $variable): RedirectResponse
    {
        $this->variableRepository->delete($variable->id);
        $this->alert->success(trans('admin/nests.variables.notices.variable_deleted', [
            'variable' => htmlspecialchars($variable->name),
        ]))->flash();

        return redirect()->route('admin.nests.egg.variables', $egg);
    }
}
