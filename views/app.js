class Recipient {
    constructor() {
        this.firstName = "";
    }
}

class RecipientsPool {
    constructor() {
        this.list = [];
    }

    add(recipient) {
        this.list.push(recipient);
    }

    deleteById(uuid) {

    }
}


USER_DIALOG = null;
PAGE_HEIGHT = null;
RECIPIENTS = [];

function displayNameOnRectangle(recipient, text) {
    var group = ELEMENTS[recipient.uuid];
    group._objects[1].text = text;

    rows = text.split('\r\n').length + 1;

    group._objects[1].top = group._objects[1].top + group.height - (10 * scale * rows) - 4;
    group.canvas.add(group);
    group.canvas.add(group._objects[1]);
}

function deleteRow(annotationId) {
    if (annotationId in ELEMENTS){
        var canvas = ELEMENTS[annotationId].canvas;
        var group = ELEMENTS[annotationId];

        ELEMENTS[annotationId].destroy();
        if (ELEMENTS[annotationId]._objects) {
            $.each(ELEMENTS[annotationId]._objects, function (index, element) {
                element.page = group.page;
                pdf.deleteObject(element);
            });
        }
        pdf.deleteObject(ELEMENTS[annotationId]);

        canvas.remove(ELEMENTS[annotationId]);
        canvas.renderAll();

        delete ELEMENTS[annotationId];

        $("rect[data-pdf-annotate-id='" + annotationId + "']").remove();
        $("li[id='" + annotationId + "']").remove();

        var list = [];
        for (var u in RECIPIENTS) {
            if (RECIPIENTS[u].uuid != annotationId) {
                list.push(RECIPIENTS[u]);
            }
        }

        RECIPIENTS = list;
    }
}

var
    // From http://www.whatwg.org/specs/web-apps/current-work/multipage/states-of-the-type-attribute.html#e-mail-state-%28type=email%29
    emailRegex = /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/,
    phoneRegex = /^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\./0-9]*$/,
    first_name = $("#first_name"),
    last_name = $("#last_name"),
    phone = $("#phone"),
    email = $("#email"),
    tips = $(".validateTips");

function updateTips(t, element) {
    swal({
        text: t,
        type: "error",
        allowOutsideClick: false
    });
}

function checkLength(o, n, min, max) {
    if (o.val().length > max || o.val().length < min) {
        updateTips("Length of " + n + " must be between " + min + " and " + max + ".", o);
        return false;
    } else {
        return true;
    }
}

function checkPhoneCountry(o) {
    if ((o.val().length > 0) && (o.val().indexOf('+') != 0)) {
        updateTips("Phone number must start with country code (+386, +385, ...)!");
        return false;
    }
    return true;
}

function checkRegexp(o, regexp, n) {
    if (!(regexp.test(o.val()))) {
        o.addClass("ui-state-error");
        updateTips(n);
        return false;
    } else {
        return true;
    }
}

function validateUser() {

    console.log(USER_DIALOG_MANDATORY_FIELDS);

    var valid = true;

    valid = valid && checkLength(first_name, "First Name", 1, 80);
    valid = valid && checkLength(last_name, "Last Name", 1, 80);
    valid = valid && checkLength(email, "Email", 6, 80);

    if (USER_DIALOG_MANDATORY_FIELDS.indexOf("phone") != -1) {
        valid = valid && checkLength(phone, "Phone", 8, 20);
    }

    if ($("#phone").val()) {
        valid = valid && checkPhoneCountry(phone);
        valid = valid && checkRegexp(phone, phoneRegex, "Please enter valid phone number.");
    }
    valid = valid && checkRegexp(email, emailRegex, "Please enter valid email address (eg. john.doe@gmail.com).");

    return valid;
}

$(function () {
    if (!DESIGNER) {
        var btns = {};
        btns[USER_DIALOG_BTN] = function (a) {

            var fld = USER_DIALOG.tag;

            if (!validateUser()) {
                return;
            }

            var data = {
                first_name: $("#first_name").val(),
                last_name: $("#last_name").val(),
                email: $("#email").val(),
                description: $("#description").val(),
                phone: $("#phone").val()
                // callingCode: $("#calling_code").val()
            };

            if ($("#type-select :selected").val() != '') {
                data['type'] = $("#type-select :selected").val();
            }

            // if (!data.callingCode)
            //   data.callingCode = '386';

            if (USER_DIALOG.rect == null) {
                let btn = Ladda.create(a.currentTarget);
                btn.start();

                updateTag(fld, data).then(function () {
                    console.log("DONE");
                    btn.stop();
                });

                // $(this).dialog("close");
                return;
            }

            // <li class="collection-item avatar">
            //       <i class="material-icons circle red">person</i>
            //       <span class="title">Title</span>
            //       <p>First Line <br>
            //   Second Line
            //   </p>
            //   <a href="#!" class="secondary-content"><i class="material-icons">delete</i></a>
            //   </li>

            // $("#recipients-list").append('<li class="collection-item avatar" id="' + fld.uuid + '"><i class="material-icons circle" style="background-color: #666666">person</i><span class="title">' + $("#first_name").val() + ' ' + $("#last_name").val() + '</span><p>' + $("#email").val() + '</p><p>' + $("#phone").val() + '</p><a href="#!" onclick=\'deleteRow(\"' + fld.uuid + '\")\' class="secondary-content"><i class="material-icons">delete</i></a></li>');
            $("#recipients-list").append('<li class="collection-item avatar" id="' + fld.uuid + '" name="' + RECIPIENTS.length + '"><i class="material-icons circle" style="background-color: #666666">person</i><span class="title">' + $("#first_name").val() + ' ' + $("#last_name").val() + '</span><p>' + $("#email").val() + '</p><p>' + $("#phone").val() + '</p><a href="#!" onclick=\'deleteRow(\"' + fld.uuid + '\")\' class="secondary-content"><i class="material-icons">delete</i></a></li>');

            RECIPIENTS.push({
                uuid: fld.uuid,
                fld: fld,
                data: data
            });
            console.log(fld);

            $("#phone").val(''); //clear
            $('#type-select option:first-child').prop('selected', true);
            $("#type-select").formSelect();

            $(this).dialog("close");
        };

        // var dialog, form;
        USER_DIALOG = $("#dialog-form").dialog({
            autoOpen: false,
            height: 800,
            width: 700,
            modal: true,
            buttons: btns,
            open: function () {
                $("#btn-main-submit").hide();
            },
            close: function (e) {
                var fld = USER_DIALOG.tag;
                var recipient = false;
                for (var u in RECIPIENTS) {
                    if (RECIPIENTS[u].uuid == fld.uuid) {
                        recipient = RECIPIENTS[u];
                        break;
                    }
                }

                if (recipient) {
                    text = recipient.data.first_name + " " + recipient.data.last_name + "\r\n" + recipient.data.email;
                    if(recipient.data.phone){
                        text += "\r\n" + recipient.data.phone;
                    }
                    displayNameOnRectangle(recipient, text)
                } else {
                    deleteRow(fld.uuid);
                }

                $('#dialog-form').find('.validate').each(function () {
                    $(this).val('');
                });

                $('#type-select option:first-child').prop('selected', true);
                $("#type-select").formSelect();

                $("#btn-main-submit").show();
                // allFields.removeClass( "ui-state-error" );
            }
        });
    } else if (DESIGNER) {
        var btns = {};
        btns[USER_DIALOG_BTN] = function (a) {
            var fld = USER_DIALOG.tag;

            if ($("#type-select").length) {
                var splitValue = $("#type-select").val().split('|');
                var signaturetag = splitValue[0];
                var signatureTagDescription = splitValue[1];
            } else {
                var signaturetag = null;
                var signatureTagDescription = $("#type-input").val();
            }

            var data = {
                signaturetag: signaturetag,
                signatureTagDescription: signatureTagDescription
            };

            $("#recipients-list").append('<li class="collection-item avatar" id="' + fld.uuid + '"><span class="title">' + signatureTagDescription + '</span><a href="#!" onclick=\'deleteRow(\"' + fld.uuid + '\")\' class="secondary-content"><i class="material-icons">delete</i></a></li>');

            RECIPIENTS.push({
                uuid: fld.uuid,
                fld: fld,
                data: data
            });
            $(this).dialog("close");
        };

        USER_DIALOG = $("#dialog-form").dialog({
            autoOpen: false,
            height: 800,
            width: 700,
            modal: true,
            buttons: btns,
            open: function () {
                $("#btn-main-submit").hide();
            },
            close: function (e) {
                var fld = USER_DIALOG.tag;
                var recipient = false;
                for (var u in RECIPIENTS) {
                    if (RECIPIENTS[u].uuid == fld.uuid) {
                        recipient = RECIPIENTS[u];
                        break;
                    }
                }

                if (recipient) {
                    displayNameOnRectangle(recipient, recipient.data.signatureTagDescription)
                } else if (found) {
                    deleteRow(fld.uuid);
                }


                $('#dialog-form').find('.validate').each(function () {
                    $(this).focus().val('').removeClass('valid').removeClass('invalid').focusout();
                });

                $('#type-select option:first-child').prop('selected', true);
                $("#type-select").formSelect();

                $("#btn-main-submit").show();
                // allFields.removeClass( "ui-state-error" );
            }
        });
    }
});
