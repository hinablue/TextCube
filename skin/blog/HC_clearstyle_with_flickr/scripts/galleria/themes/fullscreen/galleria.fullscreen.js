/*!
 * Galleria Fullscreen Theme
 * http://galleria.aino.se
 *
 * Copyright (c) 2010, Aino
 * Licensed under the MIT license.
 */

(function($) {

Galleria.themes.create({
    name: 'fullscreen',
    author: 'Galleria',
    version: '1.1',
    css: 'galleria.fullscreen.css',
    defaults: {
        transition: 'none',
        image_crop: true,
        frame: true,
        frame_color: '#fff',
        hide_dock: true
    },
    init: function(options) {
        var speed = Galleria.IE ? 0 : 200;
        var open = false;
        
        this.$('thumbnails').children().hover(function() {
            $(this).not('.active').fadeTo(speed, .4);
        }, function() {
            $(this).not('.active').fadeTo(speed, 1);
        });
        
        if (options.frame) {
            this.addElement('border');
            this.append({stage: 'border'});
        } else {
            options.frame_color = 'transparent';
        }
        
        this.$('thumbnails-list, border').css('border-color', options.frame_color);
        this.$('thumbnails, thumbnails-list, stage').css('background', options.frame_color);
        
        $(window).bind('resize', this.proxy(function() {
            var w = $(window).width();
            var h = $(window).height();
            this.rescale(w,h);
        }));
        
        if (options.hide_dock && options.thumbnails) {
            var ic = this.$('info,counter').css({
                opacity: .7,
                bottom: 10
            });
            var tc = this.$('thumbnails-container');
            var b = this.$('thumbnails').find('.galleria-image').css('height').replace('px','');
            b = (parseInt(b) + 10) * -1;
            tc.hover(function(e) {
                ic.css('bottom',10).animate({bottom: b*-1+10, opacity:1},{queue:false, duration:200});
                $(e.currentTarget).animate({bottom: 0}, {queue:false, duration: 200});
                open = true;
            }, function(e) {
                ic.animate({bottom: 10, opacity:.7},{queue:false, duration:400});
                $(e.currentTarget).animate({bottom: b}, {queue:false, duration: 400});
                open = false;
            }).css('bottom', b);
        }
        if (!options.thumbnails) {
           this.$('info,counter').css('bottom',10); 
        }

        this.$('image-nav-left, image-nav-right').css('opacity',0.01).hover(function() {
            $(this).animate({opacity:1},100);
        }, function() {
            $(this).animate({opacity:0});
        });
        
        this.bind(Galleria.LOADSTART, function(e) {
            if (!e.cached) {
                this.$('loader').show().fadeTo(100, 1);
            }
            $(e.thumbTarget).parent().addClass('active').css('opacity',.5).siblings('.active').removeClass('active').css('opacity',1);
        });

        this.bind(Galleria.LOADFINISH, function(e) {
            this.$('loader').fadeOut(300);
        });
        
        this.attachKeyboard({
            left: this.prev,
            right: this.next,
            up: function(e) {
                if (!open) {
                    tc.trigger('mouseover');
                }
                e.preventDefault();
            },
            down: function(e) {
                tc.trigger('mouseout');
                e.preventDefault();
            }
        });
    }
});

})(jQuery);