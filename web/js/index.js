$(document).foundation();

$('#from, #till').fdatepicker({
    format: 'dd-mm-yyyy',
    endDate: new Date()
}).on('changeDate', function () {
    var from = false, till = false;

    var getDate = function(selector) {
        var date = $(selector).html();
        if(date.split('&nbsp;').join('') == '--') {
            return '';
        }

        return date;
    };

    var $this = $(this);
    $this.text($this.data('date'));
    $this.fdatepicker('hide');

    getDate('#from') && (from = validate.from('#from'));
    getDate('#till') && (till = validate.till('#till'));
    validate.fromTill(from, till, '#from-till');
});

var errors = {
    container: $('#errors'),

    display: function(message, selector) {
        if(typeof message != 'undefined' && message !== null && $('.error[selector="' + selector + '"]').length == 0) {
            this.container.append('<span class="error tag" selector="'+selector+'">'+message+'</span>');
        }

        $(selector).addClass('wrong');
    },

    clear: function(selector) {
        if(typeof selector == 'undefined') {
            $('.wrong').removeClass('wrong');
            this.container.empty();
        } else {
            $(selector).removeClass('wrong');
            $('.error[selector="' + selector + '"]').remove();
        }
    }
};

var validate = {
    id: function(selector) {
        var value = $(selector).val().trim();
        if(value == '') {
            errors.display('Really need your username', selector);
            return false;
        }

        errors.clear(selector);
        return value;
    },

    _date: function(selector, message) {
        var value = $(selector).text();

        var dateRegexp = /^\d{1,2}-\d{1,2}-\d{4}$/;

        if(!value.match(dateRegexp)) {
            errors.display(message, selector);
            return false;
        }

        errors.clear(selector);
        return value;
    },

    from: function(selector, message) {
        return this._date(selector, 'From when?');
    },

    till: function(selector) {
        return this._date(selector, 'Till when?');
    },

    fromTill: function(from, till, selector) {
        if(!from || !till) {
            return false;
        }

        if(moment(from, 'DD/MM/YYYY') > moment(till, 'DD/MM/YYYY')) {
            errors.display('Dates are in the wrong order', selector);
            return false;
        } else {
            errors.clear(selector);
            return true;
        }
    }
};

function spinner(selector) {
    var $selector = $(selector);
    var html = '', step;

    step = $selector.data('spinner-step') || 3;

    if(step == 3) {
        step = 1;
    } else {
        step++;
    }

    for(var i=1; i<=3; i++) {
        html += (i != step) ? '.' : '<span class="current">.</span>';
    }

    $selector.data('spinner-step', step);
    $selector.html(html);
}

$('#id').blur(function() {
    validate.id('#id');
});

$('#proceed').click(function(e) {
    var data = {};
    var from, till, id;
    var hasErrors = false;
    var self = this;
    data.service = 'flickr';

    if($(this).hasClass('disabled')) {
        return false;
    }
    e.preventDefault();

    errors.clear();

    (data.id = validate.id('#id')) || (hasErrors = true);
    (data.from = validate.from('#from')) || (hasErrors = true);
    (data.till = validate.till('#till')) || (hasErrors = true);
    validate.fromTill(data.from, data.till, '#from-till') || (hasErrors = true);

    if(!hasErrors) {
        $('#success').slideUp();
        window.spinnerInterval = setInterval(function() {
            spinner('#proceed .spinner');
        }, 200);

        $(this).addClass('disabled');
        self = this;
        $.get('/get',data)
            .done(function(msg) {
                clearInterval(window.spinnerInterval);
                $('#proceed .spinner').text('...');
                $(self).removeClass('disabled');
                if(msg.count > 0) {
                    $('#success').html(
                        'We have found '+ msg.count +' images. Here is one at random: <img src="'+ msg.image +'" />'
                    );
                } else {
                    $('#success').html(
                        'Nothing. Barren wastes.'
                    );
                }
                $('#success').show();
            });
    }
});

