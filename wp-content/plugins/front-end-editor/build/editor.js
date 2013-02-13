// Generated by CoffeeScript 1.3.1
(function() {
  var extract_data_attr,
    __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor; child.__super__ = parent.prototype; return child; },
    __indexOf = [].indexOf || function(item) { for (var i = 0, l = this.length; i < l; i++) { if (i in this && this[i] === item) return i; } return -1; };

  extract_data_attr = function(el) {
    var attr, data, value, _i, _len, _ref;
    data = {};
    _ref = el.attributes;
    for (_i = 0, _len = _ref.length; _i < _len; _i++) {
      attr = _ref[_i];
      if (attr.specified && attr.name.indexOf('data-') === 0) {
        value = attr.value;
        try {
          value = jQuery.parseJSON(value);
        } catch (_error) {}
        if (value === null) {
          value = '';
        }
        data[attr.name.substr(5)] = value;
      }
    }
    return data;
  };

  jQuery.extend(FrontEndEditor, {
    fieldTypes: {},
    edit_lock: function($el) {
      FrontEndEditor._editing = true;
      return $el.trigger('edit_start');
    },
    edit_unlock: function($el) {
      FrontEndEditor._editing = false;
      return $el.trigger('edit_stop');
    },
    is_editing: function() {
      return FrontEndEditor._editing;
    },
    overlay: (function() {
      var $cover;
      $cover = jQuery('<div>', {
        'class': 'fee-loading'
      }).css('background-image', 'url(' + FrontEndEditor.data.spinner + ')').hide().prependTo(jQuery('body'));
      return {
        cover: function($el) {
          var bgcolor, parent, _i, _len, _ref;
          _ref = $el.parents();
          for (_i = 0, _len = _ref.length; _i < _len; _i++) {
            parent = _ref[_i];
            bgcolor = jQuery(parent).css('background-color');
            if (bgcolor !== 'transparent') {
              break;
            }
          }
          return $cover.css({
            'width': $el.width(),
            'height': $el.height(),
            'background-color': bgcolor
          }).css($el.offset()).show();
        },
        hide: function() {
          return $cover.hide();
        }
      };
    })(),
    get_group_button: function($container) {
      var $button;
      $button = $container.find('.fee-edit-button');
      if ($button.length) {
        return $button;
      }
      if (FrontEndEditor.data.add_buttons) {
        $button = jQuery('<span>', {
          "class": 'fee-edit-button',
          text: FrontEndEditor.data.edit_text
        });
        $button.appendTo($container);
        return $button;
      }
      return false;
    },
    init_fields: function() {
      var $button, $container, $elements, editor, editors, el, fieldType, _i, _j, _len, _len1, _ref, _ref1, _results;
      _ref = jQuery('.fee-group').not('.fee-initialized');
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        el = _ref[_i];
        $container = jQuery(el);
        $elements = $container.find('.fee-field').removeClass('fee-field');
        if (!$elements.length) {
          continue;
        }
        editors = (function() {
          var _j, _len1, _results;
          _results = [];
          for (_j = 0, _len1 = $elements.length; _j < _len1; _j++) {
            el = $elements[_j];
            editor = FrontEndEditor.make_editable(el);
            editor.part_of_group = true;
            _results.push(editor);
          }
          return _results;
        })();
        fieldType = $container.hasClass('status-auto-draft') ? 'createPost' : 'group';
        editor = new FrontEndEditor.fieldTypes[fieldType]($container, editors);
        $button = FrontEndEditor.get_group_button($container);
        if ($button) {
          $button.click(jQuery.proxy(editor, 'start_editing'));
          $container.bind({
            edit_start: function(ev) {
              $button.addClass('fee-disabled');
              return ev.stopPropagation();
            },
            edit_stop: function(ev) {
              $button.removeClass('fee-disabled');
              return ev.stopPropagation();
            }
          });
        } else {
          FrontEndEditor.hover_init($container, jQuery.proxy(editor, 'start_editing'));
        }
        $container.data('fee-editor', editor);
      }
      _ref1 = jQuery('.fee-field').not('.fee-initialized');
      _results = [];
      for (_j = 0, _len1 = _ref1.length; _j < _len1; _j++) {
        el = _ref1[_j];
        _results.push(FrontEndEditor.make_editable(el, true));
      }
      return _results;
    },
    make_editable: function(el, single) {
      var $el, data, editor, fieldType;
      $el = jQuery(el);
      data = extract_data_attr(el);
      $el.addClass('fee-initialized');
      fieldType = FrontEndEditor.fieldTypes[data.type];
      if (!fieldType) {
        if (console) {
          console.warn('invalid field type', el);
        }
        return;
      }
      editor = new fieldType;
      editor.el = $el;
      editor.data = data;
      if (single) {
        FrontEndEditor.hover_init($el, jQuery.proxy(editor, 'start_editing'));
        $el.data('fee-editor', editor);
      }
      return editor;
    }
  });

  FrontEndEditor.hover_init = (function() {
    var HOVER_BORDER, HOVER_PADDING, box_position_vert, get_dims, hover, hover_border, hover_box, hover_hide, hover_hide_immediately, hover_show;
    get_dims = function($el) {
      return {
        'width': $el.width(),
        'height': $el.height()
      };
    };
    HOVER_BORDER = 2;
    HOVER_PADDING = 2;
    hover = {
      lock: false,
      timeout: null
    };
    hover_border = jQuery('<div>').addClass('fee-hover-border').css('width', HOVER_BORDER).hide().appendTo('body');
    hover_box = jQuery('<div>', {
      'class': 'fee-hover-edit',
      'html': FrontEndEditor.data.edit_text,
      'mouseover': function() {
        return hover.lock = true;
      },
      'mouseout': function() {
        hover.lock = false;
        return hover_hide();
      }
    }).hide().appendTo('body');
    box_position_vert = function(mouse_vert_pos) {
      var normal_height;
      normal_height = mouse_vert_pos - hover_box.outerHeight() / 2;
      return hover_box.css('top', (normal_height - HOVER_BORDER) + 'px');
    };
    hover_hide_immediately = function() {
      hover_box.hide();
      return hover_border.hide();
    };
    hover_hide = function() {
      return hover.timeout = setTimeout(function() {
        if (hover.lock) {
          return;
        }
        return hover_hide_immediately();
      }, 300);
    };
    hover_show = function(callback) {
      var $self, dims, offset;
      $self = jQuery(this);
      offset = $self.offset();
      dims = get_dims($self);
      if (dims.width > $self.parent().width()) {
        $self.css('display', 'block');
        dims = get_dims($self);
      }
      clearTimeout(hover.timeout);
      hover_box.unbind('click');
      hover_box.bind('click', hover_hide_immediately);
      hover_box.bind('click', callback);
      hover_box.css('left', (offset.left - hover_box.outerWidth() - HOVER_PADDING) + 'px');
      hover_box.show();
      return hover_border.css({
        'left': (offset.left - HOVER_PADDING - HOVER_BORDER) + 'px',
        'top': (offset.top - HOVER_PADDING - HOVER_BORDER) + 'px',
        'height': (dims.height + HOVER_PADDING * 2) + 'px'
      }).show();
    };
    return function($el, callback) {
      return $el.bind({
        mouseover: function(ev) {
          if (FrontEndEditor.is_editing()) {
            return;
          }
          box_position_vert(ev.pageY);
          return hover_show.call(this, callback);
        },
        mousemove: function(ev) {
          return box_position_vert(ev.pageY);
        },
        mouseout: hover_hide
      });
    };
  })();

  jQuery(document).ready(function() {
    var $el, $widget, el, _i, _len, _ref;
    _ref = jQuery('[data-filter="widget_title"], [data-filter="widget_text"]');
    for (_i = 0, _len = _ref.length; _i < _len; _i++) {
      el = _ref[_i];
      $el = jQuery(el);
      $widget = $el.closest('.widget_text');
      if ($widget.length) {
        $el.attr('data-widget_id', $widget.attr('id'));
        $widget.addClass('fee-group');
      } else {
        $el.unwrap();
      }
    }
    return FrontEndEditor.init_fields();
  });

  jQuery(window).load(function() {
    var _ref;
    return (_ref = jQuery('.fee-group.status-auto-draft').data('fee-editor')) != null ? _ref.start_editing() : void 0;
  });

  FrontEndEditor.fieldTypes.base = (function() {

    base.name = 'base';

    function base() {}

    base.prototype.get_type = function() {
      return this.constructor.displayName;
    };

    base.prototype.start_editing = null;

    base.prototype.ajax_get = function() {
      FrontEndEditor.edit_lock(this.el);
      return this._ajax_request({
        data: this.ajax_get_args.apply(this, arguments),
        success: jQuery.proxy(this, 'ajax_get_handler')
      });
    };

    base.prototype.ajax_set = function() {
      return this._ajax_request({
        data: this.ajax_set_args.apply(this, arguments),
        success: jQuery.proxy(this, 'ajax_set_handler')
      });
    };

    base.prototype._ajax_request = function(args) {
      args.url = FrontEndEditor.data.ajax_url;
      args.type = 'POST';
      args.dataType = 'json';
      return jQuery.ajax(args);
    };

    base.prototype.ajax_get_handler = null;

    base.prototype.ajax_set_handler = null;

    base.prototype.ajax_get_args = function() {
      var args;
      args = this.ajax_args();
      args.callback = 'get';
      return args;
    };

    base.prototype.ajax_set_args = function(content) {
      var args;
      args = this.ajax_args();
      args.callback = 'save';
      args.content = content;
      return args;
    };

    base.prototype.ajax_args = function() {
      return {
        action: 'front-end-editor',
        nonce: FrontEndEditor.data.nonce,
        data: this.data
      };
    };

    return base;

  })();

  FrontEndEditor.fieldTypes.input = (function(_super) {

    __extends(input, _super);

    input.name = 'input';

    function input() {
      return input.__super__.constructor.apply(this, arguments);
    }

    input.prototype.input_tag = '<input type="text">';

    input.prototype.start_editing = function() {
      this.create_form();
      this.create_buttons();
      this.create_input();
      this.ajax_get();
      return false;
    };

    input.prototype.create_buttons = function() {
      this.save_button = jQuery('<button>', {
        'class': 'fee-form-save',
        'text': FrontEndEditor.data.save_text,
        'click': jQuery.proxy(this, 'submit_form')
      });
      this.cancel_button = jQuery('<button>', {
        'class': 'fee-form-cancel',
        'text': FrontEndEditor.data.cancel_text,
        'click': jQuery.proxy(this, 'remove_form')
      });
      return this.form.append(this.save_button).append(this.cancel_button);
    };

    input.prototype.create_form = function() {
      this.form = this.el.is('span') ? jQuery('<span>') : jQuery('<div>');
      this.form.addClass('fee-form').addClass('fee-type-' + this.get_type());
      return this.form.keypress(jQuery.proxy(this, 'keypress'));
    };

    input.prototype.remove_form = function() {
      this.form.remove();
      this.el.show();
      FrontEndEditor.edit_unlock(this.el);
      return false;
    };

    input.prototype.submit_form = function(ev) {
      this.ajax_set();
      return false;
    };

    input.prototype.keypress = function(ev) {
      var code, keys;
      keys = {
        ENTER: 13,
        ESCAPE: 27
      };
      code = ev.keyCode || ev.which || ev.charCode || 0;
      if (code === keys.ENTER && 'input' === this.get_type()) {
        this.save_button.click();
      }
      if (code === keys.ESCAPE) {
        return this.cancel_button.click();
      }
    };

    input.prototype.create_input = function() {
      this.input = jQuery(this.input_tag).attr({
        'id': 'fee-' + new Date().getTime(),
        'class': 'fee-form-content'
      });
      return this.input.prependTo(this.form);
    };

    input.prototype.content_to_input = function(content) {
      return this.input.val(content);
    };

    input.prototype.content_from_input = function() {
      return this.input.val();
    };

    input.prototype.content_to_front = function(content) {
      return this.el.html(content);
    };

    input.prototype.ajax_get = function() {
      FrontEndEditor.overlay.cover(this.el);
      return input.__super__.ajax_get.apply(this, arguments);
    };

    input.prototype.ajax_set_args = function(contentData) {
      FrontEndEditor.overlay.cover(this.form);
      if (0 === arguments.length) {
        contentData = this.content_from_input();
      }
      return input.__super__.ajax_set_args.call(this, contentData);
    };

    input.prototype.ajax_get_handler = function(response) {
      var $el;
      $el = this.error_handler(response);
      if (!$el) {
        return;
      }
      this.el.hide();
      $el.after(this.form);
      this.content_to_input(response.content);
      return this.input.focus();
    };

    input.prototype.ajax_set_handler = function(response) {
      var $el;
      $el = this.error_handler(response);
      if (!$el) {
        return;
      }
      this.content_to_front(response.content);
      return this.remove_form();
    };

    input.prototype.error_handler = function(response) {
      var $el, $parent;
      $parent = this.el.closest('a');
      $el = $parent.length ? $parent : this.el;
      FrontEndEditor.overlay.hide();
      if (response.error) {
        jQuery('<div class="fee-error">').append(jQuery('<span class="fee-message">').html(response.error)).append(jQuery('<span class="fee-dismiss">x</span>').click(function() {
          return $error_box.remove();
        })).insertBefore($el);
        return false;
      }
      return $el;
    };

    return input;

  })(FrontEndEditor.fieldTypes.base);

  FrontEndEditor.fieldTypes.select = (function(_super) {

    __extends(select, _super);

    select.name = 'select';

    function select() {
      return select.__super__.constructor.apply(this, arguments);
    }

    select.prototype.input_tag = '<select>';

    select.prototype.content_to_input = function(content) {
      var title, value, _i, _len, _ref, _results;
      _ref = this.data.values;
      _results = [];
      for (title = _i = 0, _len = _ref.length; _i < _len; title = ++_i) {
        value = _ref[title];
        _results.push(this.input.append(jQuery('<option>', {
          value: value,
          html: title,
          selected: content === value
        })));
      }
      return _results;
    };

    select.prototype.content_from_input = function() {
      return this.input.find(':selected').val();
    };

    return select;

  })(FrontEndEditor.fieldTypes.input);

  FrontEndEditor.fieldTypes.textarea = (function(_super) {

    __extends(textarea, _super);

    textarea.name = 'textarea';

    function textarea() {
      return textarea.__super__.constructor.apply(this, arguments);
    }

    textarea.prototype.input_tag = '<textarea rows="10">';

    return textarea;

  })(FrontEndEditor.fieldTypes.input);

  FrontEndEditor.fieldTypes.image_base = (function(_super) {
    var _ref;

    __extends(image_base, _super);

    image_base.name = 'image_base';

    function image_base() {
      return image_base.__super__.constructor.apply(this, arguments);
    }

    image_base.prototype.button_text = (_ref = FrontEndEditor.data.image) != null ? _ref.change : void 0;

    image_base.prototype.start_editing = function() {
      var _this = this;
      tb_show(this.button_text, FrontEndEditor.data.image.url);
      jQuery('#TB_closeWindowButton img').attr('src', FrontEndEditor.data.image.tb_close);
      return jQuery('#TB_iframeContent').load(function(ev) {
        var $thickbox, iframe;
        iframe = ev.currentTarget.contentWindow;
        $thickbox = iframe.jQuery(iframe.document);
        _this.thickbox_load($thickbox);
        if (jQuery.noop !== _this.media_item_manipulation) {
          $thickbox.find('.media-item').each(function(i, el) {
            return _this.media_item_manipulation(iframe.jQuery(el));
          });
          return $thickbox.ajaxComplete(function(event, request) {
            var item_id;
            item_id = jQuery(request.responseText).find('.media-item-info').attr('id');
            return _this.media_item_manipulation($thickbox.find('#' + item_id).closest('.media-item'));
          });
        }
      });
    };

    image_base.prototype.thickbox_load = function($thickbox) {
      var _this = this;
      return $thickbox.delegate('.media-item :submit', 'click', function(ev) {
        var $button, data;
        $button = jQuery(ev.currentTarget);
        data = $button.closest('form').serializeArray();
        data.push({
          name: $button.attr('name'),
          value: $button.attr('name')
        });
        data.push({
          name: 'action',
          value: 'fee_image_insert'
        });
        jQuery.post(FrontEndEditor.data.ajax_url, data, jQuery.proxy(_this, 'image_html_handler'));
        return false;
      });
    };

    image_base.prototype.media_item_manipulation = function($item) {
      $item.find('#go_button').remove();
      return $item.find(':submit').val(this.button_text);
    };

    return image_base;

  })(FrontEndEditor.fieldTypes.base);

  FrontEndEditor.fieldTypes.image = (function(_super) {

    __extends(image, _super);

    image.name = 'image';

    function image() {
      return image.__super__.constructor.apply(this, arguments);
    }

    image.prototype.start_editing = function() {
      var _this = this;
      image.__super__.start_editing.apply(this, arguments);
      return jQuery('<a id="fee-img-revert" href="#">').text(FrontEndEditor.data.image.revert).click(function(ev) {
        _this.ajax_set(-1);
        return false;
      }).insertAfter('#TB_ajaxWindowTitle');
    };

    image.prototype.media_item_manipulation = function($item) {
      $item.find('tbody tr').not('.image-size, .submit').hide();
      return image.__super__.media_item_manipulation.apply(this, arguments);
    };

    image.prototype.image_html_handler = function(html) {
      var $html;
      $html = jQuery(html);
      if ($html.is('a')) {
        $html = $html.find('img');
      }
      return this.ajax_set($html.attr('src'));
    };

    image.prototype.ajax_set_handler = function(response) {
      var url;
      url = response.content;
      if ('-1' === url) {
        return location.reload(true);
      } else {
        this.el.find('img').attr('src', url);
        return tb_remove();
      }
    };

    return image;

  })(FrontEndEditor.fieldTypes.image_base);

  FrontEndEditor.fieldTypes.thumbnail = (function(_super) {

    __extends(thumbnail, _super);

    thumbnail.name = 'thumbnail';

    function thumbnail() {
      return thumbnail.__super__.constructor.apply(this, arguments);
    }

    thumbnail.prototype.thickbox_load = function($thickbox) {
      var _this = this;
      $thickbox.find('#tab-type_url').remove();
      return $thickbox.delegate('.media-item :submit', 'click', function(ev) {
        var $item, attachment_id;
        $item = jQuery(ev.currentTarget).closest('.media-item');
        attachment_id = $item.attr('id').replace('media-item-', '');
        _this.ajax_set(attachment_id);
        return false;
      });
    };

    thumbnail.prototype.media_item_manipulation = function($item) {
      $item.find('tbody tr').not('.submit').remove();
      return thumbnail.__super__.media_item_manipulation.apply(this, arguments);
    };

    return thumbnail;

  })(FrontEndEditor.fieldTypes.image);

  if (typeof Aloha !== "undefined" && Aloha !== null) {
    Aloha.require(['aloha/selection'], function(Selection) {
      return FrontEndEditor.fieldTypes.image_rich = (function(_super) {
        var _ref;

        __extends(image_rich, _super);

        image_rich.name = 'image_rich';

        function image_rich() {
          return image_rich.__super__.constructor.apply(this, arguments);
        }

        image_rich.prototype.button_text = (_ref = FrontEndEditor.data.image) != null ? _ref.insert : void 0;

        image_rich.prototype.start_editing = function() {
          jQuery('.aloha-floatingmenu, #aloha-floatingmenu-shadow').hide();
          return image_rich.__super__.start_editing.apply(this, arguments);
        };

        image_rich.prototype.media_item_manipulation = jQuery.noop;

        image_rich.prototype.image_html_handler = function(html) {
          GENTICS.Utils.Dom.insertIntoDOM(jQuery(html), Selection.getRangeObject(), Aloha.activeEditable.obj);
          tb_remove();
          return jQuery('.aloha-floatingmenu, #aloha-floatingmenu-shadow').show();
        };

        return image_rich;

      })(FrontEndEditor.fieldTypes.image_base);
    });
  }

  FrontEndEditor.fieldTypes.rich = (function(_super) {

    __extends(rich, _super);

    rich.name = 'rich';

    function rich() {
      return rich.__super__.constructor.apply(this, arguments);
    }

    rich.prototype.content_from_input = function() {
      return Aloha.getEditableById(this.form.attr('id')).getContents();
    };

    rich.prototype.create_input = jQuery.noop;

    rich.prototype.create_form = function() {
      return this.form = Aloha.jQuery('<div class="fee-form fee-type-rich">');
    };

    rich.prototype.remove_form = function() {
      this.form.mahalo();
      return rich.__super__.remove_form.apply(this, arguments);
    };

    rich.prototype.start_editing = function(ev) {
      rich.__super__.start_editing.apply(this, arguments);
      return FrontEndEditor.current_field = this;
    };

    rich.prototype.ajax_get_handler = function(response) {
      var $el;
      $el = this.error_handler(response);
      if (!$el) {
        return;
      }
      this.create_form();
      this.form.html(response.content);
      this.el.hide();
      this.form.insertAfter($el);
      this.form.aloha();
      if (!this.part_of_group) {
        this.form.focus();
        return this.form.dblclick();
      }
    };

    return rich;

  })(FrontEndEditor.fieldTypes.textarea);

  FrontEndEditor.fieldTypes.terminput = (function(_super) {

    __extends(terminput, _super);

    terminput.name = 'terminput';

    function terminput() {
      return terminput.__super__.constructor.apply(this, arguments);
    }

    terminput.prototype.content_to_input = function(content) {
      terminput.__super__.content_to_input.apply(this, arguments);
      return this.input.suggest(FrontEndEditor.data.ajax_url + '?action=ajax-tag-search&tax=' + this.data.taxonomy, {
        multiple: true,
        resultsClass: 'fee-suggest-results',
        selectClass: 'fee-suggest-over',
        matchClass: 'fee-suggest-match'
      });
    };

    return terminput;

  })(FrontEndEditor.fieldTypes.input);

  FrontEndEditor.fieldTypes.termselect = (function(_super) {

    __extends(termselect, _super);

    termselect.name = 'termselect';

    function termselect() {
      return termselect.__super__.constructor.apply(this, arguments);
    }

    termselect.prototype.content_to_input = function(content) {
      var $dropdown;
      $dropdown = jQuery(content);
      this.input.replaceWith($dropdown);
      return this.input = $dropdown;
    };

    return termselect;

  })(FrontEndEditor.fieldTypes.select);

  FrontEndEditor.fieldTypes.widget = (function(_super) {

    __extends(widget, _super);

    widget.name = 'widget';

    function widget() {
      return widget.__super__.constructor.apply(this, arguments);
    }

    widget.prototype.create_input = jQuery.noop;

    widget.prototype.content_to_input = function(content) {
      this.input = jQuery(content);
      return this.form.prepend(content);
    };

    widget.prototype.ajax_set_args = function() {
      var args, name, value, _i, _len, _ref, _ref1;
      args = widget.__super__.ajax_set_args.apply(this, arguments);
      _ref = this.form.find(':input').serializeArray();
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        _ref1 = _ref[_i], name = _ref1.name, value = _ref1.value;
        args[name] = args[name] === void 0 ? value : jQuery.isArray(args[name]) ? args[name].concat(value) : [args[name], value];
      }
      return args;
    };

    return widget;

  })(FrontEndEditor.fieldTypes.textarea);

  FrontEndEditor.fieldTypes.group = (function(_super) {

    __extends(group, _super);

    group.name = 'group';

    function group(el, editors) {
      var editor, _i, _len, _ref;
      this.el = el;
      this.editors = editors;
      this.has_aloha = false;
      if (typeof Aloha !== "undefined" && Aloha !== null) {
        _ref = this.editors;
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          editor = _ref[_i];
          if ('rich' === editor.get_type()) {
            this.has_aloha = true;
            return;
          }
        }
      }
    }

    group.prototype.start_editing = function(ev) {
      this.create_form();
      if (this.has_aloha) {
        FrontEndEditor.current_field = this;
      } else {
        this.create_buttons();
      }
      this.ajax_get();
      return false;
    };

    group.prototype.create_form = function() {
      var editor, _i, _len, _ref;
      _ref = this.editors;
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        editor = _ref[_i];
        editor.create_form();
        editor.create_input();
      }
      group.__super__.create_form.apply(this, arguments);
      return this.el.append(this.form);
    };

    group.prototype.remove_form = function(ev) {
      var editor, _i, _len, _ref;
      _ref = this.editors;
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        editor = _ref[_i];
        editor.remove_form();
      }
      return group.__super__.remove_form.apply(this, arguments);
    };

    group.prototype.content_from_input = function() {
      var editor, _i, _len, _ref, _results;
      _ref = this.editors;
      _results = [];
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        editor = _ref[_i];
        _results.push(editor.content_from_input());
      }
      return _results;
    };

    group.prototype.keypress = jQuery.noop;

    group.prototype.ajax_set = function() {
      group.__super__.ajax_set.apply(this, arguments);
      return FrontEndEditor.overlay.cover(this.el);
    };

    group.prototype.ajax_args = function() {
      var args, commonData, data, dataArr, editor, i, item, key, value, _i, _ref;
      args = group.__super__.ajax_args.apply(this, arguments);
      args.group = true;
      dataArr = (function() {
        var _i, _len, _ref, _results;
        _ref = this.editors;
        _results = [];
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          editor = _ref[_i];
          _results.push(editor.data);
        }
        return _results;
      }).call(this);
      if (dataArr.length === 1) {
        args.data = dataArr;
      } else {
        commonData = jQuery.extend({}, dataArr[0]);
        for (i = _i = 1, _ref = dataArr.length; 1 <= _ref ? _i < _ref : _i > _ref; i = 1 <= _ref ? ++_i : --_i) {
          for (key in commonData) {
            if (!__hasProp.call(commonData, key)) continue;
            value = commonData[key];
            if (value !== dataArr[i][key]) {
              delete commonData[key];
            }
          }
        }
        args.data = (function() {
          var _j, _len, _results;
          _results = [];
          for (_j = 0, _len = dataArr.length; _j < _len; _j++) {
            data = dataArr[_j];
            item = {};
            for (key in data) {
              if (!__hasProp.call(data, key)) continue;
              if (__indexOf.call(commonData, key) < 0) {
                item[key] = data[key];
              }
            }
            _results.push(item);
          }
          return _results;
        })();
        args.commonData = commonData;
      }
      return args;
    };

    group.prototype.ajax_get_handler = function(response) {
      var editor, i, _i, _len, _ref, _ref1;
      _ref = this.editors;
      for (i = _i = 0, _len = _ref.length; _i < _len; i = ++_i) {
        editor = _ref[i];
        editor.ajax_get_handler(response[i]);
      }
      return (_ref1 = this.editors[0].input) != null ? _ref1.focus() : void 0;
    };

    group.prototype.ajax_set_handler = function(response) {
      var editor, i, _i, _len, _ref;
      _ref = this.editors;
      for (i = _i = 0, _len = _ref.length; _i < _len; i = ++_i) {
        editor = _ref[i];
        editor.ajax_set_handler(response[i]);
      }
      return this.remove_form();
    };

    return group;

  })(FrontEndEditor.fieldTypes.input);

  FrontEndEditor.fieldTypes.createPost = (function(_super) {

    __extends(createPost, _super);

    createPost.name = 'createPost';

    function createPost() {
      return createPost.__super__.constructor.apply(this, arguments);
    }

    createPost.prototype.ajax_set_args = function() {
      var args;
      args = createPost.__super__.ajax_set_args.apply(this, arguments);
      args.createPost = true;
      return args;
    };

    createPost.prototype.ajax_set_handler = function(response) {
      return window.location = response.permalink;
    };

    return createPost;

  })(FrontEndEditor.fieldTypes.group);

}).call(this);