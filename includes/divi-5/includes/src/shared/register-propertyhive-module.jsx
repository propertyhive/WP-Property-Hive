export function registerPropertyHiveModule(metadata, options) {
  const addAction = window?.vendor?.wp?.hooks?.addAction;
  const registerModule = window?.divi?.moduleLibrary?.registerModule;

  if (!addAction || !registerModule) {
    return;
  }

  addAction(
    'divi.moduleLibrary.registerModuleLibraryStore.after',
    metadata?.name || 'propertyhive.module',
    () => {
      const moduleConfig = {
        metadata,
        renderers: {
          edit: options.preview,
        },
      };

      if (options.settingsContent || options.settingsDesign || options.settingsAdvanced) {
        moduleConfig.settings = {};

        if (options.settingsContent) {
          moduleConfig.settings.content = options.settingsContent;
        }

        if (options.settingsDesign) {
          moduleConfig.settings.design = options.settingsDesign;
        }

        if (options.settingsAdvanced) {
          moduleConfig.settings.advanced = options.settingsAdvanced;
        }
      }

      registerModule(metadata, moduleConfig);
    }
  );
}