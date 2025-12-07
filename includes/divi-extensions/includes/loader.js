import React from 'react';
import PropertyActions from './modules/PropertyActions/PropertyActions.jsx';

(function($) {
  console.log('PH Divi bundle loaded', window.location.href);

  // Wrap the React component in a plain module definition object
  const PropertyActionsModule = {
    // Slug must match PHP $slug
    slug: PropertyActions.slug || 'et_pb_property_actions_widget',

    // Divi will call this render() with props – we return JSX
    render: function(props) {
      return <PropertyActions {...props} />;
    },
  };

  $(window).on('et_builder_api_ready', function(event, API) {
    console.log('et_builder_api_ready fired', API, PropertyActionsModule);

    if (!API || !API.Modules || typeof API.Modules.register !== 'function') {
      return;
    }

    API.Modules.register([
      PropertyActionsModule,
    ]);
  });
})(window.jQuery);
