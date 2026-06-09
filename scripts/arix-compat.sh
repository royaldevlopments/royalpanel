#!/bin/bash
# Arix Compatibility Module for Blueprint Framework Fork
# Detects Arix Theme, backs it up, restores, and merges Blueprint directives.

ARIX_BACKUP_DIR="$FOLDER/.blueprint/arix-backup"

# Ensure FOLDER is set (falls back to current directory when sourced standalone)
if [ -z "$FOLDER" ]; then
  FOLDER="$(realpath "$(dirname "$0" 2>/dev/null)" 2>/dev/null || pwd)"
fi

# ──────────────────────────────────────────────
# Detect if Arix Theme is installed
# ──────────────────────────────────────────────
detect_arix() {
  if [ -d "$FOLDER/arix" ]; then
    ARIX_VERSION=$(ls "$FOLDER/arix/" 2>/dev/null | sort -V | tail -1)
    if [ -n "$ARIX_VERSION" ] && [ -d "$FOLDER/arix/$ARIX_VERSION" ]; then
      export ARIX_VERSION
      return 0
    fi
  fi
  return 1
}

# ──────────────────────────────────────────────
# Arix source path helper
# ──────────────────────────────────────────────
arix_src() {
  echo "$FOLDER/arix/$ARIX_VERSION"
}

# ──────────────────────────────────────────────
# Backup current Arix-influenced files
# ──────────────────────────────────────────────
backup_arix() {
  detect_arix || return 0
  PRINT INFO "Backing up Arix theme files..."
  rm -rf "$ARIX_BACKUP_DIR"
  mkdir -p "$ARIX_BACKUP_DIR"

  cp "$FOLDER/app/Http/ViewComposers/AssetComposer.php" "$ARIX_BACKUP_DIR/AssetComposer.php" 2>/dev/null
  cp "$FOLDER/resources/views/templates/wrapper.blade.php" "$ARIX_BACKUP_DIR/wrapper.blade.php" 2>/dev/null
  cp "$FOLDER/resources/views/layouts/admin.blade.php" "$ARIX_BACKUP_DIR/admin.blade.php" 2>/dev/null

  PRINT SUCCESS "Arix files backed up"
}

# ──────────────────────────────────────────────
# Restore Arix React components and PHP/template files
# from the arix/ source directory
# ──────────────────────────────────────────────
restore_arix_scripts() {
  detect_arix || return 0
  local src
  src=$(arix_src)

  if [ -d "$src/resources/scripts" ]; then
    PRINT INFO "Restoring Arix React components..."
    rsync -a "$src/resources/scripts/" "$FOLDER/resources/scripts/"
  fi
}

restore_arix_php() {
  detect_arix || return 0
  local src
  src=$(arix_src)

  if [ -f "$src/app/Http/ViewComposers/AssetComposer.php" ]; then
    PRINT INFO "Restoring Arix AssetComposer..."
    cp "$src/app/Http/ViewComposers/AssetComposer.php" "$FOLDER/app/Http/ViewComposers/AssetComposer.php"
  fi
}

restore_arix_templates() {
  detect_arix || return 0
  local src
  src=$(arix_src)

  if [ -f "$src/resources/views/templates/wrapper.blade.php" ]; then
    PRINT INFO "Restoring Arix wrapper template..."
    cp "$src/resources/views/templates/wrapper.blade.php" "$FOLDER/resources/views/templates/wrapper.blade.php"
  fi

  if [ -f "$src/resources/views/layouts/admin.blade.php" ]; then
    PRINT INFO "Restoring Arix admin template..."
    cp "$src/resources/views/layouts/admin.blade.php" "$FOLDER/resources/views/layouts/admin.blade.php"
  fi
}

# ──────────────────────────────────────────────
# Add Blueprint's navigation React imports/components
# to Arix's NavigationBar.tsx
# ──────────────────────────────────────────────
merge_navigationbar() {
  detect_arix || return 0
  local nav="$FOLDER/resources/scripts/components/NavigationBar.tsx"

  if [ ! -f "$nav" ]; then return 0; fi

  if grep -q "BeforeNavigation" "$nav" 2>/dev/null; then
    PRINT INFO "Blueprint navigation hooks already present"
    return 0
  fi

  PRINT INFO "Adding Blueprint navigation hooks to NavigationBar.tsx..."

  # Add Blueprint imports after the Avatar import
  sed -i "/^import Avatar from/a import BeforeNavigation from '@blueprint\/components\/Navigation\/NavigationBar\/BeforeNavigation';\nimport AdditionalItems from '@blueprint\/components\/Navigation\/NavigationBar\/AdditionalItems';\nimport AfterNavigation from '@blueprint\/components\/Navigation\/NavigationBar\/AfterNavigation';" "$nav"

  # Add id to wrapper div
  sed -i "s/className={'w-full bg-neutral-900 shadow-md overflow-x-auto'}/className={'w-full bg-neutral-900 shadow-md overflow-x-auto'} id={'NavigationBar'}/" "$nav"

  # Add BeforeNavigation after wrapper div opening
  sed -i "/id={'NavigationBar'}/a\            <BeforeNavigation />" "$nav"

  # Add AfterNavigation before last </div>
  sed -i "/<\/div>$/a\            <AfterNavigation />" "$nav"

  # Add id to nav links and AdditionalItems
  sed -i "s|<NavLink to={'/'} exact>|<NavLink to={'/'} exact id={'NavigationDashboard'}>|" "$nav"
  sed -i "s|<a href={'/admin'} rel={'noreferrer'}>|<a href={'/admin'} rel={'noreferrer'} id={'NavigationAdmin'}>|" "$nav"
  sed -i "s|<NavLink to={'/account'}>|<NavLink to={'/account'} id={'NavigationAccount'}>|" "$nav"
  sed -i "s|<button onClick={onTriggerLogout}>|<button onClick={onTriggerLogout} id={'NavigationLogout'}>|" "$nav"

  # Add AdditionalItems after the admin link
  sed -i "/id={'NavigationAdmin'}/a\                    <AdditionalItems />" "$nav"

  PRINT SUCCESS "Blueprint navigation hooks merged into NavigationBar.tsx"
}

# ──────────────────────────────────────────────
# Merge Blueprint @include/@yield directives
# into Arix Blade templates
# ──────────────────────────────────────────────
merge_template_directives() {
  detect_arix || return 0
  local modified=0

  # ── wrapper.blade.php ──
  local wrapper="$FOLDER/resources/views/templates/wrapper.blade.php"
  if [ -f "$wrapper" ]; then
    local need_wrapper_rewrite=false

    if ! grep -q "@include('blueprint.dashboard.dashboard')" "$wrapper" 2>/dev/null; then
      need_wrapper_rewrite=true
    fi

    if [ "$need_wrapper_rewrite" = true ]; then
      PRINT INFO "Adding Blueprint directives to wrapper.blade.php..."
      # Insert at line 1
      sed -i "1s/^/@include('blueprint.dashboard.dashboard')\n@yield('blueprint.lib')\n/" "$wrapper"
      ((modified++))
    fi

    if ! grep -q "@yield('blueprint.wrappers')" "$wrapper" 2>/dev/null; then
      sed -i "/@section('content')/a @yield('blueprint.wrappers')" "$wrapper"
      ((modified++))
    fi
  fi

  # ── admin.blade.php ──
  local admin="$FOLDER/resources/views/layouts/admin.blade.php"
  if [ -f "$admin" ]; then
    if ! grep -q "@include(\"blueprint.admin.admin\")" "$admin" 2>/dev/null; then
      PRINT INFO "Adding Blueprint directives to admin.blade.php..."
      sed -i "1s/^/@include(\"blueprint.admin.admin\")\n@yield('blueprint.lib')\n/" "$admin"
      ((modified++))
    fi

    if ! grep -q "@yield(\"blueprint.import\")" "$admin" 2>/dev/null; then
      sed -i "/<\/head>/i @yield(\"blueprint.import\")" "$admin"
      ((modified++))
    fi

    if ! grep -q "@yield('blueprint.cache')" "$admin" 2>/dev/null; then
      sed -i "/<body class/i @yield('blueprint.cache')" "$admin"
      ((modified++))
    fi

    if ! grep -q "@yield(\"blueprint.navigation\")" "$admin" 2>/dev/null; then
      sed -i "/class=\"user-menu\"/a @yield(\"blueprint.navigation\")" "$admin"
      ((modified++))
    fi

    if ! grep -q "@yield(\"blueprint.sidenav\")" "$admin" 2>/dev/null; then
      sed -i "/<li class=\"header\">MANAGEMENT/a @yield(\"blueprint.sidenav\")" "$admin"
      ((modified++))
    fi

    if ! grep -q "@yield('blueprint.introduction')" "$admin" 2>/dev/null; then
      sed -i "/@section('content-header')/i @yield('blueprint.introduction')" "$admin"
      ((modified++))
    fi

    if ! grep -q "@yield('blueprint.wrappers')" "$admin" 2>/dev/null; then
      echo "@yield('blueprint.wrappers')" >> "$admin"
      ((modified++))
    fi
  fi

  if [ "$modified" -gt 0 ]; then
    PRINT SUCCESS "Added $modified Blueprint directive(s) to Arix templates"
  fi
}

# ──────────────────────────────────────────────
# Merge Blueprint config into Arix's AssetComposer
# Adds the 'blueprint' key to siteConfiguration
# ──────────────────────────────────────────────
merge_asset_composer() {
  detect_arix || return 0
  local composer="$FOLDER/app/Http/ViewComposers/AssetComposer.php"

  if [ ! -f "$composer" ]; then return 0; fi

  if grep -q "'blueprint' =>" "$composer" 2>/dev/null; then
    PRINT INFO "Blueprint config already in AssetComposer"
    return 0
  fi

  if ! grep -q "SettingsRepositoryInterface" "$composer" 2>/dev/null; then
    PRINT WARNING "AssetComposer does not use SettingsRepositoryInterface, skipping merge"
    return 0
  fi

  PRINT INFO "Adding Blueprint config to AssetComposer..."

  # Find the end of the siteConfiguration array and inject blueprint config before it
  sed -i "/^\\s*];\$/i\            'blueprint' => [\n                'disable_attribution' => \\\$this->settings->get('settings::blueprint:flags:disable_attribution', '0') === '1',\n            ]," "$composer"

  PRINT SUCCESS "Blueprint config added to AssetComposer"
}

# ──────────────────────────────────────────────
# Full Arix compatibility run
# ──────────────────────────────────────────────
arix_full_install() {
  if ! detect_arix; then
    PRINT INFO "Arix not detected, skipping compatibility layer"
    return 0
  fi

  PRINT INFO "Applying Arix compatibility layer..."

  # 1. Backup current files
  backup_arix

  # 2. Restore Arix source files
  restore_arix_scripts
  restore_arix_php
  restore_arix_templates

  # 3. Merge Blueprint directives into restored Arix files
  merge_navigationbar
  merge_template_directives
  merge_asset_composer

  PRINT SUCCESS "Arix compatibility applied"
}
