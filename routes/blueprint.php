<?php

use Illuminate\Support\Facades\Route;
use RoyalPanel\Http\Controllers\Admin;
use RoyalPanel\BlueprintFramework\Libraries\ExtensionLibrary\Console\BlueprintConsoleLibrary as BlueprintExtensionLibrary;

$blueprint = app()->make(BlueprintExtensionLibrary::class);

/*
|--------------------------------------------------------------------------
| Blueprint Extensions
|--------------------------------------------------------------------------
|
| Endpoint: /admin/extensions
|
*/
Route::group(['prefix' => 'extensions'], function () {
  Route::get('/', [Admin\ExtensionsController::class, 'index'])->name('admin.extensions');
});

Route::group(['prefix' => 'extensions/blueprint'], function () {
  /* Blueprint admin page */
  Route::patch('/', [Admin\Extensions\Blueprint\BlueprintExtensionController::class, 'update']);

  /* Extension permissions endpoint */
  Route::patch('/config', [RoyalPanel\BlueprintFramework\Controllers\ExtensionConfigurationController::class, 'update']);
});

foreach ($blueprint->extensionsConfigs() as $extension) {
  $extension = $extension['info'];
  $identifier = $extension['identifier'];
  $controllerName = $identifier . 'ExtensionController';
  
  Route::group(['prefix' => 'extensions/' . $identifier], function () use ($identifier, $controllerName) {
    $controllerClass = "RoyalPanel\\Http\\Controllers\\Admin\\Extensions\\{$identifier}\\{$controllerName}";
    
    Route::get('/', [$controllerClass, 'index'])->name("admin.extensions.{$identifier}.index");
    Route::patch('/', [$controllerClass, 'update'])->name("admin.extensions.{$identifier}.patch");
    Route::post('/', [$controllerClass, 'post'])->name("admin.extensions.{$identifier}.post");
    Route::put('/', [$controllerClass, 'put'])->name("admin.extensions.{$identifier}.put");
    Route::delete('/{target}/{id}', [$controllerClass, 'delete'])->name("admin.extensions.{$identifier}.delete");
  });
}