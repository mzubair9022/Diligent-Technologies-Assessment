(function(wp) {
    var registerBlockType = wp.blocks.registerBlockType;
    var ServerSideRender = wp.components.ServerSideRender;
  
    registerBlockType('my-gutenberg-block/my-gutenberg-block', {
      title: 'My Gutenberg Block',
      icon: 'admin-post',
      category: 'common',
  
      edit: function(props) {
        return wp.element.createElement(ServerSideRender, {
          block: 'my-gutenberg-block/my-gutenberg-block',
          attributes: props.attributes
        });
      },
  
      save: function() {
        return null;
      }
    });
  })(window.wp);
  