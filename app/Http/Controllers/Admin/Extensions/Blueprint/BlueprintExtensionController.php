<?php

namespace RoyalPanel\Http\Controllers\Admin\Extensions\Blueprint;

use Illuminate\Http\RedirectResponse;
use Database\Seeders\BlueprintSeeder;
use Illuminate\Support\Facades\Artisan;
use RoyalPanel\Http\Controllers\Controller;
use RoyalPanel\Http\Requests\Admin\AdminFormRequest;
use RoyalPanel\Contracts\Repository\SettingsRepositoryInterface;

class BlueprintExtensionController extends Controller
{

  /**
   * BlueprintExtensionController constructor.
   */
  public function __construct(
    private SettingsRepositoryInterface $settings,
  ) {
  }

  /**
   * @throws \RoyalPanel\Exceptions\Model\DataValidationException
   * @throws \RoyalPanel\Exceptions\Repository\RecordNotFoundException
   */
  public function update(BlueprintAdminFormRequest $request): RedirectResponse
  {
    $meta_flag = $this->settings->get('blueprint::flags:remote_metadata');

    foreach ($request->validated() as $key => $value) {
      $this->settings->set('blueprint::' . $key, $value);
    }

    // refresh meta if the flag has been altered
    if($meta_flag != $request->validated()['flags:remote_metadata']) {
      Artisan::call('bp:meta');
    }

    return redirect()->route('admin.extensions');
  }
}

class BlueprintAdminFormRequest extends AdminFormRequest
{
  public function rules(): array
  {
    // Get schema to determine types
    $seeder = app(BlueprintSeeder::class);
    $schema = $seeder->getSchema();

    $rules = [];
    foreach ($schema['flags'] as $key => $config) {
      $flagPath = "flags:{$key}";

      // Build validation rules based on type
      switch ($config['type']) {
        case 'boolean':
          $rules[$flagPath] = 'boolean';
          break;
        case 'string':
          $rules[$flagPath] = 'string|nullable';
          break;
        case 'number':
          $rules[$flagPath] = 'numeric';
          break;
        case 'integer':
          $rules[$flagPath] = 'integer';
          break;
      }
    }

    return $rules;
  }
}
