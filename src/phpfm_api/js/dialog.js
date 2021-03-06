var Dialog = {
    restApiUrl: 'func/rest.api.php',

    funcBg: null,
    funcDialog: null,

    /**
     * Dummy.
     */
    dummy: function () {
        return;
    },

    /**
     * Init func dialog.
     */
    initFuncDialog: function (title, oper, redirect, closable, secondaryFolder) {
        if (Dialog.funcBg == null) {
            Dialog.funcBg = $('<div/>');
            Dialog.funcBg.attr('id', 'funcBg');
            $('#content').append(Dialog.funcBg);
        }
        if (Dialog.funcDialog == null) {
            Dialog.funcDialog = $('<div/>');
            Dialog.funcDialog.attr('id', 'funcDialog');
            var divHeader = $('<div/>');
            divHeader.addClass('divHeader');
            Dialog.funcDialog.append(divHeader);
            var divInput = $('<div/>');
            divInput.attr('id', 'divInput');
            divInput.addClass('container');
            var form = $('<form/>');
            var actionUrl = secondaryFolder ? ('../' + Dialog.restApiUrl) : Dialog.restApiUrl;
            form.attr({
                action: actionUrl,
                method: 'post',
                enctype: 'multipart/form-data'
            });
            divInput.append(form);
            Dialog.funcDialog.append(divInput);
            $('#content').append(Dialog.funcDialog);
        }

        Dialog.funcDialog.find('.divHeader').html('').append($('<span/>').html(title));
        var imgSrc = secondaryFolder ? '../images/close.png' : 'images/close.png';
        if (closable) {
            Dialog.funcDialog.find('.divHeader').append(
                $('<a/>').attr('href', 'javascript:;').addClass('funcClose').append(
                    $('<img/>').attr({
                        alt: 'Close',
                        src: imgSrc,
                        border: '0'
                    })).click(Dialog.closeFunc));
            Dialog.funcDialog.addClass('closable');
            Dialog.funcBg.click(Dialog.closeFunc);
        } else {
            Dialog.funcDialog.removeClass('closable');
            Dialog.funcBg.click(Dialog.dummy);
        }

        var form = Dialog.funcDialog.find('form');
        form.html('');
        form.append($('<input/>').attr({
            type: 'hidden',
            id: 'oper',
            name: 'oper'
        }).val(oper));
        if (redirect) {
            form.append($('<input/>').attr({
                type: 'hidden',
                id: 'return',
                name: 'return'
            }).val(Strings['return']));
        } else {
            form.append($('<input/>').attr({
                type: 'hidden',
                name: 'noredirect'
            }).val('noredirect'));
        }

        form.unbind('submit');

        return form;
    },

    setFocus: function (o) {
        o.focus();
        o.get(0).select();
    },

    /**
     * Display submit part.
     */
    displaySubmit: function () {
        if (Dialog.funcDialog == null) {
            return;
        }
        var div = $('<div/>').addClass('funcBtnLine');
        div.append($('<input/>').attr('type', 'submit').val(Strings['OK']));
        if (Dialog.funcDialog.hasClass('closable')) {
            var buttonCancel = $('<input/>').attr('type', 'button').val(Strings['Cancel']);
            buttonCancel.click(Dialog.closeFunc);
            div.append(buttonCancel);
        }
        Dialog.funcDialog.find('form').append(div);
    },

    /**
     * Display func dialog.
     */
    displayFuncDialog: function () {
        if (Dialog.funcBg == null || Dialog.funcDialog == null) {
            return;
        }

        Dialog.funcBg.css('height', document.documentElement.scrollHeight + 'px');
        Dialog.funcBg.css('display', 'block');
        Dialog.funcDialog.css('left', (document.documentElement.clientWidth - 420) / 2 + 'px');
        Dialog.funcDialog.fadeIn('fast');
    },

    /**
     * Close func dialog.
     */
    closeFunc: function () {
        var funcAudioPlayer = Dialog.funcDialog.find('div#funcAudioPlayer');
        if (funcAudioPlayer.length && funcAudioPlayer.is(':visible')) {
            AudioPlayer.close('divAudioPlayer'); // IE 9 has a bug on this call
            //funcAudioPlayer.fadeOut();
        }

        if (Dialog.funcDialog.is(':visible')) {
            Dialog.funcDialog.fadeOut();
        }

        Dialog.funcBg.css('display', 'none');
    }

};
