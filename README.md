# Genesis Child Theme

Proof of concept for how to handle automatic updates in WordPress child themes - without losing user customizations.

It requires theme authors to store all of the updatable code in a specific directory. This could be `vendor`, `core`, `lib`, `do-not-edit`, a combination of these or whatever the theme author wants to call it. When an update runs, only these directories are changed, all other files are untouched - except for the stylesheet, which needs to have the version number bumped.

In this example project, the `core` and `vendor` directories are updatable. This means that they contain all of the code that might be updated in future versions. Below is a list of which files can and cannot be edited by the user:

```shell
child-theme-name/          # → ✅ Root directory
├── core/                  # → ❌ Core directory
│   ├── core-functions.php # → ❌ Core functions
│   ├── core-scripts.js    # → ❌ Core scripts
│   ├── core-styles.css    # → ❌ Core styles
│   └── core-template.php  # → ❌ Example page template
├── vendor/                # → ❌ Composer packages
├── functions.php          # → ✅ Autoloader and code snippets
├── screenshot.png         # → ✅ Theme screenshot
└── style.css              # → ✅ User code snippets
```

This project has additional functions in place to hide the non-editable folders from the WordPress Theme Editor. This will prevent the majority of users from finding and editing these files.


## How it works

_Before Update_

1. Duplicate entire child theme in themes directory. E.g `genesis-sample-backup-1.0.0`. If successful, continue update.

_After Update_

2. Bump version number in the backup theme stylesheet.
3. Copy original files back to new version, replacing everything __except__ for the updatable folder.

## Installation

Follow the steps below to test out this cool theme:

1. Install the Genesis Framework parent theme on your test site.
2. Download [child theme zip file](https://github.com/seothemes/genesis-child-theme/archive/master.zip), upload it and activate it.
3. Navigate to Appearance → Theme Editor and change the style.css version to 1.0.0.
4. Add some custom CSS to style.css and/or a code snippet to functions.php.
5. Optionally rename the child theme to test white-labeling feature.
6. Navigate to Appearance → Themes. There should now be an update showing.
7. Click update and once it has finished refresh the page.
8. There should now be 2 themes, the new version and the backup version.
9. Navigate back to Appearance → Theme Editor and check that your code snippets remained.
10. Check that the version number has been updated in the style.css file.

If the version number was updated and your customizations were not lost, then the update was successful!

## Troubleshooting

__Update not available__

If you have changed the theme version number back to 1.0.0 and there are still no updates showing, follow the steps below:

1. Install [Debug Bar](https://wordpress.org/plugins/debug-bar/).
2. Click the "Debug" menu in the Admin Bar (a.k.a Toolbar).
3. Open the "PUC (your-slug)" panel.
4. Click the "Check Now" button.

__Testing core and vendor update__

To check whether or not the `core` and `vendor` directory were updated successfully, open one of the files in either directory in a text editor and add some commented out code, e.g:

```php
// This line will be removed if the update worked.
```

After running an update, open up the modified file and see if the line is still there. If the update worked, it should be gone. The line will still be in the backup version of the theme.


## Considerations

- If the update is successful but the backup isn't, then the users changes will be lost. There are checks in place to avoid this but some extra steps might be required.

- The updatable folder should be hidden from the WP File Editor using the `theme_scandir_exclusions` filter. This would stop a large amount of users from finding it.

- There still needs to be one line of code in functions.php to include the folder with the standard `// Start the engine (do not remove).` comment above. This should be clear enough to users.

- Checking for updates - we can use the WordPress.org repository or [Plugin Update Checker](https://github.com/YahnisElsts/plugin-update-checker) for free themes, [EDD Software Licensing](https://easydigitaldownloads.com/downloads/software-licensing/) or [Freemius](https://freemius.com/) for premium licensed themes. StudioPress could also create an update API for their child themes, similar to what Genesis already has.

### Pros

- Child theme can be used for its intended purpose of storing code snippets.
- Theme author and user can access template hierarchy.
- Child theme can be renamed/white-labelled without affecting updates.
- Automatic backup is made during update - can be deleted by user if not needed.
- Removes the need for storing code in a separate plugin, and maintaining multiple repos.
- Better option than having a `customizations` folder (more flexible, templates work correctly etc).
- Same workflow for users, install parent theme, install child theme. No other steps required.

### Cons

_User Education_

- Most people already assume automatic updates are not possible in child themes.
- User might delete updatable folder by accident.
- If user edits updatable folder, e.g `vendor`, modifications in that folder will be lost.
- If user removes the `require_once` statement in functions.php, the theme won't work.

_Minor Issues_

- If backup fails but update succeeds, customizations will be lost. Most likely preventable with some additional checks.
- Theme authors will need to take an extra step to use the standard template hierarchy.
- Theme authors need to write pluggable/hookable code. Nothing should be hardcoded. This could be considerred a Pro depending on how you look at it.

## Contributing

If you find any issues or have any questions about this project, feel free to [create a new issue](https://github.com/seothemes/genesis-child-theme/issues/new) on this repository.
