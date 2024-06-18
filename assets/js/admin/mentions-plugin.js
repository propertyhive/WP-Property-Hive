(function() {
    tinymce.create('tinymce.plugins.mentions', {
        init: function(ed, url) {
            ed.on('init', function() {
                var contentArea = jQuery(ed.getDoc()).find('body');
                contentArea.attr('contenteditable', 'true');

                var tribute = new Tribute({
                    trigger: '@', // Ensure @ is set as the trigger character
                    values: function(text, cb) {
                        console.log('Fetching mentions for:', text); // Debug log
                        jQuery.ajax({
                            url: ajaxurl, // Use WordPress AJAX URL
                            method: 'POST',
                            data: {
                                action: 'fetch_mentions',
                                query: text
                            },
                            success: function(response) {
                                console.log('Mentions fetched:', response); // Debug log
                                cb(response);
                            },
                            error: function(error) {
                                console.log('Error fetching mentions:', error); // Debug log
                            }
                        });
                    },
                    selectTemplate: function(item) {
                        return '@' + item.original.name;
                    },
                    lookup: 'name',
                    fillAttr: 'name',
                    menuItemLimit: 5, // Limit number of items displayed
                    noMatchTemplate: function() {
                        return '<li>No matches found</li>';
                    }
                });

                tribute.attach(contentArea.get(0));
                console.log('Tribute attached:', contentArea.get(0)); // Debug log

                // Debug Tribute.js events
                contentArea.on('tribute-active-true', function() {
                    console.log('Tribute activated'); // Debug log
                });

                contentArea.on('tribute-active-false', function() {
                    console.log('Tribute deactivated'); // Debug log
                });

                contentArea.on('tribute-replaced', function() {
                    if (tribute.menuContainer) {
                        tribute.menuContainer.classList.add('tribute-container');
                        console.log('Menu container initialized'); // Debug log
                    }
                });

                // Debug keyup and click events
                contentArea.on('keyup click', function(event) {
                    console.log('Keyup or click event detected:', event); // Debug log
                    console.log('Current content:', contentArea.text()); // Debug log

                    if (tribute.isActive) {
                        console.log('Tribute is active'); // Debug log
                        var caretPos = contentArea.caret('offset');
                        var menuContainer = jQuery('.tribute-container');
                        if (menuContainer.length > 0) {
                            menuContainer.css({
                                top: caretPos.top + 20, // Adjust position as needed
                                left: caretPos.left
                            });
                        }
                    } else {
                        console.log('Tribute is not active'); // Debug log
                    }
                });
            });
        },
        getInfo: function() {
            return {
                longname: 'Mentions Plugin',
                author: 'Your Name',
                version: '1.0'
            };
        }
    });
    // Register plugin
    tinymce.PluginManager.add('mentions', tinymce.plugins.mentions);
})();
