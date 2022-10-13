app = {
    request: JSON.parse('<?php echo $request?>'),
    entity: JSON.parse('<?php echo $entity?>'),
    entityRelate: JSON.parse('<?php echo $entityrelated?>'),
    user: JSON.parse('<?php echo $user?>'),
    permission: JSON.parse('<?php echo $permission?>'),
    method: '<?php echo $method?>',
    editor: {},
    dataoperator: JSON.parse('<?php echo $dataoperator?>'),
    operatorchar: JSON.parse('<?php echo $operatorchar?>'),
    searchcurent: '',
    SERVERFILE: '<?php echo SERVERFILE?>',
    recordid: 0,
    dirtemp: 'temp' + Date.now(),
    recordData: null,
    importData: {},
    countAjax: 0,
    isEditing: false,
    isReload: true,
    opener,
    setEntity: function (entityid) {
        var result = this.getDataAwait('?route=Core/Entity/getEntity&id=' + entityid);
        try {
            result = JSON.parse(result);
            if (result.statuscode == 1) {
                this.entity = result.data;
            }
        } catch (e) {

        }
    },
    controlReady: function () {
        /*$('.datetimepicker').bootstrapMaterialDatePicker({
            format: 'DD-MM-YYYY HH:mm:ss'
        });
        // Date Picker
        jQuery('.mydatepicker').datepicker({
            autoclose: true,
            todayHighlight: true,
            format: 'dd-mm-yyyy'
        });*/
        // Clock pickers
        $('.clockpicker').clockpicker({
            donetext: 'Done'
        }).find('input').change(function () {
            console.log(this.value);
        });
        $('.js-switch').each(function () {
            new Switchery($(this)[0], $(this).data());
        });
        $(".keyvalue").sortable({
            placeholder: "ui-state-highlight",
        }).disableSelection();
        $(".listFileSelect").sortable({
            placeholder: "ui-state-highlight",
        }).disableSelection();
        $('.selectpicker').selectpicker('refresh');
        common.numberReady();
        $('[datatype=BOOLEAN]').click(function () {
            console.log($(this).attr('id'));
            if ($(this).prop('checked')) {
                $('[name=' + $(this).attr('id') + ']').val(1);
            } else {
                $('[name=' + $(this).attr('id') + ']').val(0);
            }
        });
    },
    showImportForm: function () {
        $('#modalImportForm').modal({
            backdrop: 'static',
            keyboard: false
        });
        $('#modalImportForm .modal-title').html("Nhập từ file - " + app.entity.entityname);
        //$('#modalQuickForm .modal-body').load();
    },
    popupQuickInsertForm:function (entitytype,entityname,classname,callback) {
        $('#modalQuickForm').modal();
        $('#modalQuickForm .modal-title').html(entityname);
        $('#modalQuickForm .modal-body').load('?route=' + entitytype + '/' + classname + '/Insert', function () {
            $('#frmQuick [datatype=relatedto]').each(function () {
                app.genRelateControl2($(this), $(this).attr('entityrelated'));
            });
            $('#frmQuick [datatype=relatedtomulti]').each(function () {
                app.genRelateControl2($(this), $(this).attr('entityrelated'));
            });
            $('#frmQuick [datatype=optionset]').each(function () {
                var element = $(this);
                var optionsetid = element.attr('optionsetid');
                app.genOptionSetControl(element, optionsetid);
            });
            $('#frmQuick [datatype=optionsetmulti]').each(function () {
                var element = $(this);
                var optionsetid = element.attr('optionsetid');
                app.genOptionSetControl(element, optionsetid);
            });

            $('.datetimepicker').bootstrapMaterialDatePicker({
                format: 'DD-MM-YYYY HH:mm'
            });
            // Date Picker
            jQuery('.mydatepicker').datepicker({
                autoclose: true,
                todayHighlight: true,
                format: 'dd-mm-yyyy'
            });
            // Clock pickers
            $('.clockpicker').clockpicker({
                donetext: 'Done',
            }).find('input').change(function () {

            });
            common.numberReady();
            callback();
            $(document).triggerAll('showQuickInsertLoadComplete');
        });
        $('#modalQuickForm #btnQuickFormSave').unbind('click');
        $('#modalQuickForm #btnQuickFormSave').click(function () {
            common.showLoading();
            app.saveFormInputCustom(entitytype,classname,'frmQuick',function (result) {
                common.endLoading();
                $('.form-group').removeClass('has-danger');
                $('.form-control-feedback').html('');
                if (result.statuscode) {
                    toastr["success"](result.text, "");
                    if (app.entity.structure == 'list') {
                        window.location = '?route=' + entitytype + '/' + classname + '/Edit&id=' + result.data.id;
                    } else {
                        window.location.reload();
                    }

                } else {

                    var arr = new Array();
                    for (var i in result.data) {
                        arr.push(result.data[i])
                        $('#frmQuick #col_' + i).addClass('has-danger');
                        $('#frmQuick #infor_' + i).html(result.data[i]);
                    }
                    toastr["error"](arr.join('<br>'), result.text);
                }
            });
        });
    },
    showQuickInsertForm: function (callback) {
        if (Number(app.entity['notinsrertquick']) == 1) {
            window.location = '?route=' + app.entity.entitytype + '/' + app.entity.classname + '/Insert';
        } else {
            this.popupQuickInsertForm(app.entity.entitytype,app.entity.entityname,app.entity.classname,callback);
        }

    },
    detail: function (recordid) {
        var strmenu = '';
        if (app.request.menuid != undefined) {
            strmenu = '&menuid=' + app.request.menuid;
        }
        //this.opener = window.open('?route=' + this.entity.entitytype + '/' + this.entity.classname + '/View&id=' + recordid+strmenu);
        window.location = '?route=' + this.entity.entitytype + '/' + this.entity.classname + '/View&id=' + recordid + strmenu;
        // var timer = setInterval(function () {
        //     if (app.opener.closed) {
        //         clearInterval(timer);
        //         common.showLoading();
        //         window.location.reload();
        //     }
        // }, 1000);
    },
    openRelateTab: function (url) {
        window.location = url;
        // this.opener = window.open(url);
        // var timer = setInterval(function () {
        //     if (app.opener.closed) {
        //         clearInterval(timer);
        //         common.showLoading();
        //         window.location.reload();
        //     }
        // }, 1000);
    },
    edit: function () {
        var strmenu = '';
        if (app.request.menuid != undefined) {
            strmenu = '&menuid=' + app.request.menuid;
        }
        window.location = '?route=' + this.entity.entitytype + '/' + this.entity.classname + '/Edit&id=' + this.request.id + strmenu;
    },
    deleteItem: function () {
        var id = this.recordid;
        //var name = $('#modalQuickView .modal-title').html();
        $.confirm({
            title: 'Confirm!',
            content: 'Bạn có muốn xóa?',
            buttons: {
                yes: {
                    text: 'Yes', // text for button
                    btnClass: 'btn-red', // class for the button
                    keys: ['enter', 'y'], // keyboard event for button
                    isHidden: false, // initially not hidden
                    isDisabled: false, // initially not disabled
                    action: function () {
                        $.getJSON("?route=" + app.entity.entitytype + "/" + app.entity.classname + "/Delete&id=" + id, function (result) {
                            if (result.statuscode == 1) {
                                //window.opener.location.reload();
                                history.back();
                            } else {
                                toastr["error"](result.text, "Error");
                            }
                        })
                    }
                },
                no: function () {

                },
            }
        });
    },
    showAddCondition: function () {
        $('#modalAddCondition').modal();
    },
    loadAtributeCodition: function () {
        var str = '<option value=""></option>';
        for (var i in this.entity.attributes) {
            if (app.dataoperator[this.entity.attributes[i].datatype] != undefined) {
                str += '<option tablename="' + app.entity.tablename + '" attributeid="' + this.entity.attributes[i].id + '" entityrelated="' + this.entity.attributes[i].entityrelated + '" optionsetid="' + this.entity.attributes[i].optionsetid + '" datatype="' + this.entity.attributes[i].datatype + '" value="' + this.entity.attributes[i].attributename + '">' + this.entity.attributes[i].attributelabel + '</option>';
            }
        }
        for (var i in this.entity.coreattributes) {
            if (app.dataoperator[this.entity.coreattributes[i].datatype] != undefined) {
                str += '<option tablename="' + app.entity.tablename + '" entityrelated="' + this.entity.coreattributes[i].entityrelated + '" datatype="' + this.entity.coreattributes[i].datatype + '" value="' + this.entity.coreattributes[i].attributename + '">' + this.entity.coreattributes[i].attributelabel + '</option>';
            }
        }
        $('#modalAddCondition #listAttribute').html(str);
    },
    getAttribute: function (attributeid) {
        var attribute = {};
        for (var i in this.entity.attributes) {
            if (this.entity.attributes[i].id == attributeid) {
                attribute = this.entity.attributes[i];
            }
        }
        return attribute;
    },
    loadListbyParent: function (parentid, sortordercol) {
        var attributeparent = this.getAttribute(this.entity['parentcol']);
        var parentcolname = attributeparent.attributename;
        var result = app.getDataAwait("?route=" + this.entity.entitytype + "/" + this.entity.classname + "/get" + this.entity.classname + "s&sortcol=" + sortordercol + "&sorttype=asc&" + parentcolname + "=equal_" + parentid);
        result = JSON.parse(result);
        return result;
    },
    loadListbyParent1: function (parentid, sortordercol, callback) {
        var attributeparent = this.getAttribute(this.entity['parentcol']);
        var parentcolname = attributeparent.attributename;
        $.get("?route=" + this.entity.entitytype + "/" + this.entity.classname + "/get" + this.entity.classname + "s&sortcol=" + sortordercol + "&sorttype=asc&" + parentcolname + "=equal_" + parentid, function (result) {
            result = JSON.parse(result);
            callback(result);
        });

    },
    getOptionSet: function (optionsetid) {
        var result = app.getDataAwait('?route=Core/OptionSet/getOptionSet&id=' + optionsetid);
        return JSON.parse(result);
    },
    getAttributePermission: function (attributename) {
        var permission = 'hide';
        for (const i in app.entity.attributes) {
            if (app.entity.attributes[i].attributename == attributename) {
                permission = app.entity.attributes[i].permission;
            }
        }
        return permission;
    },
    itemReady: function (element, val) {
        element.attr('data-value', val);
        var eid = element.attr('id');

        switch (element.attr('datatype')) {
            case 'TEXT':
                val = common.strReplace('\\r\\n', '\n', val);
                element.val(val);
                break;
            case 'BOOLEAN':
                if (val == 1) {
                    element.prop('checked', true)
                    $('#frm' + app.entity.classname + ' [name=' + element.attr('id') + ']').val(1);
                }
                break;
            case 'DATE':
                if (val != '0000-00-00' && val != '') {
                    var dt = new Date(val);
                    //element.datepicker('update', common.dateShow(dt));
                    element.val(val);
                }
                break;
            case 'DATETIME':
                if (val != '0000-00-00 00:00:00' && val != '') {
                    element.val(common.converToLocalTime(val));
                }
                break;
            case 'TIME':
                element.val(common.timeView(val));
                break;
            case 'image':
                element.attr('data-default-file', '<?php echo IMAGESERVER?>autosize-500x500/upload/' + app.entity.tablename + '/' + app.recordid + '/' + val);
                $('#' + element.attr('id') + '_value').val(val);
                break;
            case 'file':
                element.val(val);
                var col = element.attr('name');
                $('#frm' + app.entity.classname + ' #col_' + col + ' .listFileSelect').html('<div class="clearfix list-group-item" filename="' + val + '" basename="' + val + '">' + val + '<button type="button" onclick="app.removeFile($(this),\'' + col + '\')" class="btn btn-sm btn-danger float-right"><i class="fa fa-trash"></i></button></div>');
                break;
            case 'video':
                element.val(val);
                var col = element.attr('name');
                $('#frm' + app.entity.classname + ' #col_' + col + ' .listFileSelect').html('<div class="clearfix list-group-item" filename="' + val + '" basename="' + val + '"><video style="width: 100%" src="' + app.SERVERFILE + 'upload/' + app.entity.tablename + '/' + app.recordid + '/' + val + '" controls=""></video><button type="button" onclick="app.removeFile($(this),\'' + col + '\')" class="btn btn-sm btn-danger float-right"><i class="fa fa-trash"></i></button></div>');
                break;
            case 'relatedto':
            case 'relatedtomulti':

                app.genRelateControl2(element);
                // if (entityid != 35 && entityid != 36) {
                //     app.genRelateControl(element, entityid);
                // }
                break;
            case 'optionset':
            case 'optionsetmulti':
                var optionsetid = element.attr('optionsetid');
                app.genOptionSetControl(element, optionsetid);
                break;
            case 'PASSWORD':

                break;
            case 'attachment':
                element.val(val);
                app.loadAttachment(element.attr('id'));
                break;
            case 'imagemulti':
                element.val(val);
                app.loadImageMulti(element.attr('id'));
                break;
            case 'code':
                ace.require("ace/ext/language_tools");
                var col = element.attr('name');
                app.editor = ace.edit("codeeditor_" + col);
                app.editor.setTheme("ace/theme/tomorrow");
                var mode = 'html';

                if (app.recordData != null && app.recordData.type != undefined) {
                    mode = app.recordData.type;
                }
                if (app.entity.classname == 'Process') {
                    mode = 'php';
                }
                if (element.attr('code_language') != '' && element.attr('code_language') != undefined) {
                    mode = element.attr('code_language');
                }
                app.editor.session.setMode("ace/mode/" + mode);
                app.editor.setOptions({
                    enableBasicAutocompletion: true,
                    enableSnippets: true,
                    enableLiveAutocompletion: true
                });
                app.editor.setValue(common.decodeBase64Unicode(val));
                break;

            default:
                element.val(val);
        }
        var permission = app.getAttributePermission(eid);
        if (permission == 'hide') {
            $('#col_' + eid).remove();
        }
        if (permission == 'read') {
            element.prop('disabled', true);
        }
    },
    openQuickFrom: function (entityid, itemid, callback) {
        common.showLoading();
        var result = app.getDataAwait('?route=Core/Entity/getEntity&id=' + entityid);
        result = JSON.parse(result);
        var entityview = result.data;
        app.entityRelate = result.data;
        var item = null;
        var title = '';
        if (itemid != undefined) {
            var result = app.getDataAwait('?route=' + entityview.entitytype + '/' + entityview.classname + '/get' + entityview.classname + '&id=' + itemid);
            result = JSON.parse(result);
            if (result.statuscode) {
                item = result.data;
            }
            title = 'Cập nhật';
        } else {
            title = 'Thêm mới';
        }
        console.log(item);
        $('#modalQuickForm').modal();
        $('#modalQuickForm .modal-title').html(title);
        $('#modalQuickForm .modal-body').load('?route=' + entityview.entitytype + '/' + entityview.classname + '/loadForm', function () {
            if (item != null) {
                $('#frm' + entityview.classname + ' #id').val(item.id)
            }
            for (var i in entityview.attributes) {
                var col = entityview.attributes[i].attributename;
                var val = '';
                if (item != null) {
                    val = item[col]
                } else {
                    if (entityview.attributes[i].entityrelated == app.entity.id) {
                        val = app.recordid;
                    }
                }
                app.itemReady($('#frm' + entityview.classname + ' #' + col), val);
            }
            if ($('#frm' + entityview.classname + ' #assignees').length) {
                app.itemReady($('#frm' + entityview.classname + ' #assignees'), '');
            }
            $(document).triggerAll('quickFormLoadComplete');
            app.controlReady();
            $('#modalQuickForm #btnQuickFormSave').unbind('click');
            $('#modalQuickForm #btnQuickFormSave').click(function () {
                common.showLoading();
                app.saveFormInput(entityview.entitytype, entityview.classname, function (datesave) {
                    console.log(datesave);
                    if (datesave.statuscode) {
                        callback();
                    }

                });

                // app.postData('?route=' + entityview.entitytype + '/' + entityview.classname + '/Save', $('#frm' + entityview.classname).serialize(), function (result) {
                //     common.endLoading();
                //     if (result.statuscode) {
                //         toastr["success"](result.text, "Success");
                //         $('#modalQuickForm').modal('hide');
                //         //app.loadDataTableView(tableviewelement, true);
                //         callback();
                //     } else {
                //         var arr = new Array();
                //         for (var i in result.data) {
                //             arr.push(result.data[i])
                //         }
                //         toastr["error"](arr.join('<br>'), result.text);
                //     }
                // });
            });
            $('.notallowedit').parent().remove();
            $('[datatype=PASSWORD]').parent().remove()
            $('[datatype=image]').parent().remove()
            $('[datatype=attachment]').parent().remove()
            $('[datatype=imagemulti]').parent().remove()
            $('[datatype=file]').parent().remove()
            $('[datatype=video]').parent().remove()
            if ($(".textarea_editor").length > 0) {
                tinymce.init({
                    selector: "textarea.textarea_editor",
                    //theme: "modern",
                    height: 300,
                    plugins: [
                        "advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker",
                        "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
                        "save table contextmenu directionality emoticons template paste textcolor"
                    ],
                    toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | l      ink image | print preview media fullpage | forecolor backcolor emoticons",

                });
            }
            app.ajaxComplete(function () {
                for (var i in entityview.attributes) {
                    if (entityview.attributes[i].entityrelated == app.entity.id) {
                        $('#frm' + entityview.classname + ' #' + entityview.attributes[i].attributename).selectpicker('val', app.request.id);
                    }
                }
                common.endLoading();
                $(document).triggerAll('loadFormReady');
                console.log('loadFormReady');
            })
        });
    },
    ajaxComplete: function (callback) {
        var timer = setInterval(function () {
            if (app.countAjax == 0) {
                clearInterval(timer);
                callback();
            }
        }, 1000);
    },
    deleteTableViewItem: function (element) {
        var tableviewid = element.attr('tableviewid');
        var itemid = element.attr('itemid');
        var entityid = $('#' + tableviewid).attr('entityid');
        var result = app.getDataAwait('?route=Core/Entity/getEntity&id=' + entityid);
        result = JSON.parse(result);
        var entityview = result.data;
        $.confirm({
            title: 'Confirm!',
            content: 'Do you want to delete?',
            buttons: {
                yes: {
                    text: 'Yes', // text for button
                    btnClass: 'btn-red', // class for the button
                    keys: ['enter', 'y'], // keyboard event for button
                    isHidden: false, // initially not hidden
                    isDisabled: false, // initially not disabled
                    action: function () {
                        $.getJSON("?route=" + entityview.entitytype + "/" + entityview.classname + "/Delete&id=" + itemid, function (result) {
                            if (result.statuscode == 1) {
                                app.loadDataTableView($('#' + tableviewid), true);
                            } else {
                                toastr["error"](e.responseText, "Error");
                            }
                        })
                    }
                },
                no: function () {

                },
            }
        });
    },
    editTableViewItem: function (element) {
        var tableviewid = element.attr('tableviewid');
        var itemid = element.attr('itemid');
        app.openQuickFrom($('#' + tableviewid).attr('entityid'), itemid, function () {
            app.loadDataTableView($('#' + tableviewid), true);
        });
    },
    genCellView: function (itemid, value, attributeid, attributename, datatype, entityrelated, optionsetid) {
        var td = '';
        switch (datatype) {
            case 'relatedto':
            case 'relatedtomulti':
                var val = app.getDataAwait("?route=" + app.entity.entitytype + "/" + app.entity.classname + "/getReqRelatedValue", {
                    valueid: value,
                    entityrelated: entityrelated
                });
                td += '<td itemid="' + itemid + '" attributeid="' + attributeid + '" attributename="' + attributename + '" datatype="' + datatype + '" entityrelated="' + entityrelated + '" optionsetid="' + optionsetid + '" data-value="' + value + '">' + val + '</td>';
                break;
            case 'optionset':
            case 'optionsetmulti':
                var val = app.getDataAwait("?route=" + app.entity.entitytype + "/" + app.entity.classname + "/getReqOptionSetValue", {
                    key: value,
                    optionsetid: optionsetid,
                    attributeid: attributeid
                });
                td += '<td itemid="' + itemid + '" attributeid="' + attributeid + '" attributename="' + attributename + '" datatype="' + datatype + '" entityrelated="' + entityrelated + '" optionsetid="' + optionsetid + '" data-value="' + value + '">' + val + '</td>';
                break;
            case 'INT':
            case 'BIGINT':
            case 'DOUBLE':
            case 'FLOAT':
                var val = common.formateNumber(value);
                td += '<td class="number" itemid="' + itemid + '" attributeid="' + attributeid + '" attributename="' + attributename + '" datatype="' + datatype + '" entityrelated="' + entityrelated + '" optionsetid="' + optionsetid + '" data-value="' + value + '">' + val + '</td>';
                break;
            case 'DATETIME':
                var val = common.dateTimeShow(value);
                td += '<td class="text-center" itemid="' + itemid + '" attributeid="' + attributeid + '" attributename="' + attributename + '" datatype="' + datatype + '" entityrelated="' + entityrelated + '" optionsetid="' + optionsetid + '" data-value="' + value + '">' + val + '</td>';
                break;
            case 'DATE':
                var val = common.dateShow(value);
                td += '<td class="text-center" itemid="' + itemid + '" attributeid="' + attributeid + '" attributename="' + attributename + '" datatype="' + datatype + '" entityrelated="' + entityrelated + '" optionsetid="' + optionsetid + '" data-value="' + value + '">' + val + '</td>';
                break;
            case 'BOOLEAN':
                var val = '';
                if (value == 1) {
                    val = '<i class="fa fa-check"></i>';
                }
                td += '<td class="text-center" itemid="' + itemid + '" attributeid="' + attributeid + '" attributename="' + attributename + '" datatype="' + datatype + '" entityrelated="' + entityrelated + '" optionsetid="' + optionsetid + '" data-value="' + value + '">' + val + '</td>';
                break;
            case 'image':
                var val = '<img src="<?php echo IMAGESERVER?>resizepng-100x100/upload/' + app.entityRelate.tablename + '/' + itemid + '/' + value + '">';
                td += '<td itemid="' + itemid + '" attributeid="' + attributeid + '" attributename="' + attributename + '" datatype="' + datatype + '" entityrelated="' + entityrelated + '" optionsetid="' + optionsetid + '" data-value="' + value + '">' + val + '</td>';
                break;
            default:
                var val = value;
                td += '<td itemid="' + itemid + '" attributeid="' + attributeid + '" attributename="' + attributename + '" datatype="' + datatype + '" entityrelated="' + entityrelated + '" optionsetid="' + optionsetid + '" data-value="' + value + '">' + val + '</td>';
        }
        return td;
    },
    loadDataTableView: function (element, isEdit) {
        var entityid = element.attr('entityid');
        var res = app.getDataAwait('?route=Core/Entity/getEntity&id=' + entityid);
        res = JSON.parse(res);
        var entityview = res.data;
        var condition = element.attr('condition');
        var arr = condition.split(' ');
        condition = arr[0] + '=' + arr[1] + '_' + app.recordData[arr[2]];
        var res = app.getDataAwait('?route=' + entityview.entitytype + '/' + entityview.classname + '/get' + entityview.classname + 's&' + condition);
        try {
            res = JSON.parse(res);
            var data = res.data;
            var tbody = '';
            for (var i in data) {
                var tddata = '';
                for (var j = 0; j < element.find('th').length; j++) {
                    var eldom = element.find('th')[j];
                    if (eldom.getAttribute('attributename') != null) {
                        var attributeid = eldom.getAttribute('attributeid');
                        var attributename = eldom.getAttribute('attributename');
                        var datatype = eldom.getAttribute('datatype');
                        var entityrelated = eldom.getAttribute('entityrelated');
                        var optionsetid = eldom.getAttribute('optionsetid');
                        tddata += app.genCellView(data[i].id, data[i][attributename], attributeid, attributename, datatype, entityrelated, optionsetid);
                    }
                }
                tbody += '<tr>';
                tbody += '<td class="text-center">' + (Number(i) + 1) + '</td>';
                tbody += tddata;
                if (isEdit == true) {
                    tbody += '<td class="text-center"><button type="button" class="btn btn-success btn-sm" tableviewid="' + element.attr('id') + '" itemid="' + data[i].id + '" onclick="app.editTableViewItem($(this))"><i class="fa fa-pencil"></i></button>' +
                        '<button type="button" class="btn btn-danger btn-sm" tableviewid="' + element.attr('id') + '" itemid="' + data[i].id + '" onclick="app.deleteTableViewItem($(this))"><i class="fa fa-remove"></i></button></td></tr>';
                }

            }
            if (isEdit == false) {
                element.find('th').last().remove()
            }
            $('#' + element.attr('id') + ' tbody').html(tbody);
            element.triggerAll('afterloadDataTableView');
        } catch (e) {

        }
    },
    fillData: function () {
        if (app.recordData != null) {
            for (var col in app.recordData) {
                if ($('#frm' + app.entity.classname + ' #' + col).length > 0) {
                    app.itemReady($('#frm' + app.entity.classname + ' #' + col), app.recordData[col]);
                }
            }
        } else {
            $('#frm' + app.entity.classname + ' .form-control').each(function () {
                app.itemReady($(this), '');
            });
        }

        $('.dropify').dropify();
        common.numberReady();
        $('.dropify-clear').click(function () {
            var eid = $(this).parent().parent().children()[0].id;
            $('#' + eid).val('');
        });
        $('#frm' + app.entity.classname + ' [datatype=keyvalue]').each(function () {
            var val = $(this).val().replaceAll('\"', '"');
            if (val != '') {
                try {
                    var data = JSON.parse(val);
                    for (var key in data) {
                        app.addKeyValue($(this).attr('name'), key, data[key]);
                    }
                } catch (e) {
                    console.log(e);
                }

            }

        });

        $('.hlcontrol').each(function () {
            if ($(this).attr('control') == 'tableview') {
                $(this).append('<button type="button" class="btn btn-success btnTableViewAddRow"><i class="fa fa-plus"></i> Add Row</button>');
                $('.btnTableViewAddRow').click(function () {
                    var element = $(this).parent();
                    app.openQuickFrom($(this).parent().attr('entityid'), undefined, function () {
                        app.loadDataTableView(element, true);
                    });
                });
                $('#' + $(this).attr('id') + ' table').append('<tbody></tbody>')
                app.loadDataTableView($(this), true);
            }
        });
        if ($(".textarea_editor").length > 0) {
            tinymce.init({
                selector: "textarea.textarea_editor",
                //theme: "modern",
                height: 300,
                plugins: [
                    "advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker",
                    "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
                    "save table contextmenu directionality emoticons template paste textcolor"
                ],
                toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | l      ink image | print preview media fullpage | forecolor backcolor emoticons",

            });
        }
        app.ajaxComplete(function () {
            $(document).triggerAll('loadFormReady');
            console.log('loadFormReady');
            common.endLoading()
            setTimeout(function () {
                $('.form-control').change(function () {
                    app.isEditing = true;
                });
            }, 5000)
        });

    },
    loadFormData: function () {
        console.log('loadFormData');
        if (this.entity.classname != undefined) {
            common.showLoading();
            $('.notallowedit').prop('disabled', true);
            if (this.request.id != undefined) {
                $.getJSON("?route=" + this.entity.entitytype + "/" + this.entity.classname + "/get" + this.entity.classname + "&id=" + this.request.id, function (result) {
                    if (result.statuscode) {
                        app.recordData = result.data;
                        app.recordid = app.recordData.id;
                        app.fillData();
                        $('title').html(app.recordData[app.entity.mainattribute.attributename]);
                    } else {
                        window.location = HTTPSERVER;
                    }
                });
            } else {
                app.fillData();
            }

        }
    },
    addKeyValue: function (attributename, key, value) {
        var str = '<li class="row">' +
            '<div class="col-5"><input type="text" class="form-control optionsetkey" value="' + (key != undefined ? key : '') + '"></div>' +
            '<div class="col-5"><input type="text" class="form-control optionsetvalue" value="' + (value != undefined ? value : '') + '"></div>' +
            '<div class="col-1"><button type="button" class="btn btn-danger" onclick="$(this).parent().parent().remove()"><i class="fa fa-remove"></i></div>' +
            '</li>';
        $('#keyvalule_' + attributename).append(str);
    },
    parseKeyValue: function (attributename) {
        var arrKey = new Array();
        $('#keyvalule_' + attributename + ' .optionsetkey').each(function () {
            arrKey.push($(this).val())
        });
        var arrValue = new Array();
        $('#keyvalule_' + attributename + ' .optionsetvalue').each(function () {
            arrValue.push($(this).val())
        });
        if (arrKey.length) {
            var data = new Object()
            for (var i in arrKey) {
                data[arrKey[i]] = arrValue[i];
            }
            return JSON.stringify(data);
        } else {
            return '';
        }
    },
    genOptionSetControlData: function (element, optionSet) {
        var str = '<option value=""></option>';
        for (var key in optionSet) {
            str += '<option value="' + key + '">' + optionSet[key] + '</option>';
        }
        element.html(str);
        element.selectpicker('refresh');
        if (element.attr('data-value') != undefined) {
            element.selectpicker('val', common.stringToArray(element.attr('data-value')));
        }
    },
    genOptionSetControl: function (element, optionsetid) {
        var optionSet;
        if (optionsetid != 0) {
            if (!isNaN(Number(optionsetid))) {
                var result = app.getOptionSet(optionsetid);
                try {
                    optionSet = JSON.parse(result.data.optionsetvalue);
                } catch (e) {
                    console.log(e);
                }
            } else {
                var result = app.getDataAwait('?route=Core/OptionSet/getList&optionsetname=equal_' + encodeURIComponent(optionsetid));
                try {
                    optionSetList = JSON.parse(result);
                    try {
                        optionSet = JSON.parse(optionSetList.data[0].optionsetvalue);
                    } catch (e) {
                        console.log(e);
                    }
                } catch (e) {
                    console.log(e);
                }
            }

        } else {
            optionSet = JSON.parse(common.decodeBase64Unicode(element.attr('optionsetdata')));
            if (typeof (optionSet) == 'string') {
                optionSet = JSON.parse(optionSet);
            }
            console.log(optionSet);
        }
        var str = '<option value=""></option>';
        for (var key in optionSet) {
            str += '<option value="' + key + '">' + optionSet[key] + '</option>';
        }
        element.html(str);
        element.selectpicker('refresh');
        if (element.attr('data-value') != undefined) {
            element.selectpicker('val', common.stringToArray(element.attr('data-value')));
            // try {
            //     element.selectpicker('val', JSON.parse(element.attr('data-value')));
            // } catch (e) {
            //     element.selectpicker('val', element.attr('data-value'));
            // }

        }
    },
    genRelateControl2: function (element, entityid) {
        if (entityid == null) {
            entityid = element.attr('entityrelated');
        }
        if (entityid != undefined) {
            $.getJSON('?route=Core/Entity/getEntity&id=' + entityid, function (result) {
                if (result.statuscode) {
                    app.genRelateControl(element, result.data);
                }

            });
        } else {
            var entitytype = element.attr('entitytype');
            var classname = element.attr('classname');
            $.getJSON('?route=Core/Entity/getEntity&type=' + entitytype + '&classname=' + classname, function (result) {
                if (result.statuscode) {
                    app.genRelateControl(element, result.data);
                }

            });
        }


    },
    genRelateControl: function (element, entitydata) {
        element.select2({
            ajax: {
                url: "?route=" + entitydata.entitytype + "/" + entitydata.classname + "/Search",
                processResults: function (data) {
                    // Transforms the top-level key of the response object from 'items' to 'results'
                    return {
                        results: data
                    };
                }
            },
            cache: true,
            allowClear: true,
            placeholder: 'Chọn giá trị',
        });
        if (element.attr('datatype') == 'relatedtomulti') {
            var vals = common.stringToArray(element.attr('data-value'));
            if (vals.length) {
                for (const i in vals) {
                    $.getJSON("?route=" + entitydata.entitytype + "/" + entitydata.classname + "/ShowSearchItem&id=" + vals[i], function (result) {
                        if (result.statuscode) {
                            element.append('<option value="' + vals[i] + '" selected="selected">' + result.text + '</option>');
                        }
                    })
                }
            }
        } else {
            if (Number(element.attr('data-value'))) {
                $.getJSON("?route=" + entitydata.entitytype + "/" + entitydata.classname + "/ShowSearchItem&id=" + element.attr('data-value'), function (result) {
                    if (result.statuscode) {
                        element.html('<option value="' + element.attr('data-value') + '" selected="selected">' + result.text + '</option>');
                    }
                })
            } else {
                if (entitydata.structure == 'tree') {
                    element.html('<option value="0" selected="selected"></option>');
                }
            }
        }
    },
    curent: {col: '', datatype: ''},
    copyClipboard: function (copyText) {
        var tempInput = document.createElement("input");
        tempInput.value = copyText;
        document.body.appendChild(tempInput);
        tempInput.select();
        document.execCommand("copy");
        document.body.removeChild(tempInput);
        $('#modalFile').modal('hide');
    },
    openLibrary: function (col, datatype, formid) {
        app.curent.col = col;
        app.curent.datatype = datatype;
        $.getJSON('?route=Common/File&tablename=' + this.entity.tablename + '&id=' + (this.recordid == 0 ? this.dirtemp : this.recordid), function (files) {
            var str = '';
            for (var i in files) {
                switch (datatype) {
                    case 'LONGTEXT':
                        str += '<tr>' +
                            '<td>' + files[i]['display'] + ' ' + files[i]['basename'] + '</td>' +
                            '<td class="text-center middle"><button type="button" class="btn btn-success" col="' + col + '" basename="' + files[i]['basename'] + '" filename="' + files[i]['filename'] + '" onclick="app.copyClipboard(\'' + files[i]['truepath'] + '\')"><i class="fa fa-copy"></i></button>' +
                            '<button type="button" class="btn btn-danger" filename="' + files[i]['filename'] + '" col="' + col + '" datatype="' + datatype + '" onclick="app.deleteFile($(this))"><i class="fa fa-remove"></i></button></td>' +
                            '</tr>';
                        break;
                    default:
                        str += '<tr>' +
                            '<td>' + files[i]['display'] + '<button type="button" class="btn btn-success" col="' + col + '" basename="' + files[i]['basename'] + '" filename="' + files[i]['filename'] + '" onclick="app.copyClipboard(\'' + files[i]['truepath'] + '\')"><i class="fa fa-copy"></i></button></td>' +
                            '<td class="text-center middle"><button type="button" class="btn btn-success" col="' + col + '" basename="' + files[i]['basename'] + '" filename="' + files[i]['filename'] + '" onclick="app.selectFile($(this),\'' + datatype + '\',\'' + formid + '\')"><i class="fa fa-check"></i></button>' +
                            '<button type="button" class="btn btn-danger" filename="' + files[i]['filename'] + '" col="' + col + '" datatype="' + datatype + '" onclick="app.deleteFile($(this))"><i class="fa fa-remove"></i></button></td>' +
                            '</tr>';
                }

            }
            var table = '<table class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">' +
                str + '</table>';

            $('#modalFile .modal-body #listfile').html(table);
            $('#modalFile').modal('show');
            $(document).triggerAll('openPopupFile');
        })
    },
    deleteFile: function (element) {
        var filename = element.attr('filename');
        var col = element.attr('col');
        var datatype = element.attr('datatype');
        var result = app.getDataAwait('?route=Common/File/Delete&filename=' + filename)
        result = JSON.parse(result);
        if (result.statuscode) {
            toastr["success"](result.text, "");
            app.openLibrary(col, datatype);
        } else {
            toastr["error"](result.text, "Error");
        }
    },
    viewFile: function () {

    },
    selectFile: function (element, datatype, formid) {
        if (formid == "undefined") {
            formid = 'frm' + app.entity.classname;
        }
        switch (datatype) {
            case 'attachment':
                var col = element.attr('col');
                var filename = element.attr('filename');
                var basename = element.attr('basename');

                $('#' + formid + ' #col_' + col + ' .listFileSelect').append('<div class="clearfix list-group-item" filename="' + filename + '" basename="' + basename + '">' + basename + '<button type="button" onclick="app.removeAttachment($(this),\'' + col + '\',\'' + formid + '\')" class="btn btn-sm btn-danger float-right"><i class="fa fa-trash"></i></button></div>');
                app.updateAttachment(col, formid);
                toastr["success"]('Bạn đã chọn ' + basename, "Thành công");
                break;
            case 'imagemulti':
                var col = element.attr('col');
                var filename = element.attr('filename');
                var basename = element.attr('basename');

                $('#' + formid + ' #col_' + col + ' .listFileSelect').append('<div class="col-md-3 text-center" filename="' + filename + '" basename="' + basename + '">' +
                    '<img src="<?php echo IMAGESERVER?>autosize-0x200/' + filename + '"><input type="text" placeholder="Title" class="form-control imagetitle"><textarea class="form-control imagesummary"></textarea><input type="text" placeholder="Link" class="form-control imagelink">' +
                    '<button type="button" onclick="app.removeAttachment($(this),\'' + col + '\')" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button></div>');

                toastr["success"]('Bạn đã chọn ' + basename, "Thành công");
                break;
            case 'image':
                var col = element.attr('col');
                var filename = element.attr('filename');
                var basename = element.attr('basename');
                console.log('#' + formid + ' #col_' + col + ' .dropify-render img');
                if ($('#' + formid + ' #col_' + col + ' .dropify-render img').length == 0) {
                    $('#' + formid + ' #col_' + col + ' .dropify-render').html('<img>');
                }
                $('#' + formid + ' #' + col).attr('data-default-file', '<?php echo FILESERVER?>' + filename);
                $('#' + formid + ' #' + col + '_value').val(basename);
                $('#' + formid + ' #col_' + col + ' .dropify-render img').attr('src', '<?php echo FILESERVER?>' + filename);
                $('#modalFile').modal('hide');
                break;
            case 'file':
                var col = element.attr('col');
                var filename = element.attr('filename');
                var basename = element.attr('basename');
                $('#' + formid + ' #col_' + col + ' .listFileSelect').html('<div class="clearfix list-group-item" filename="' + filename + '" basename="' + basename + '">' + basename + '<button type="button" onclick="app.removeFile($(this),\'' + col + '\')" class="btn btn-sm btn-danger float-right"><i class="fa fa-trash"></i></button></div>');
                $('#' + formid + ' #' + col).attr('data-value', basename);
                $('#' + formid + ' #' + col).val(basename);
                $('#modalFile').modal('hide');
                break;
            case 'video':
                var col = element.attr('col');
                var filename = element.attr('filename');
                var basename = element.attr('basename');
                $('#' + formid + ' #col_' + col + ' .listFileSelect').html('<div class="clearfix list-group-item" filename="' + filename + '" basename="' + basename + '"><video style="width: 100%" src="' + app.SERVERFILE + filename + '" controls></video><button type="button" onclick="app.removeFile($(this),\'' + col + '\')" class="btn btn-sm btn-danger float-right"><i class="fa fa-trash"></i></button></div>');
                $('#' + formid + ' #' + col).attr('data-value', basename);
                $('#' + formid + ' #' + col).val(basename);
                $('#modalFile').modal('hide');
                break;
            case 'callback':

                break;
        }

    },
    loadAttachment: function (col) {
        var strAttachment = $('#frm' + app.entity.classname + ' #' + col).val();
        try {
            var arr = JSON.parse(strAttachment);
            for (var i = 0; i < arr.length; i++) {

                $('#frm' + app.entity.classname + ' #col_' + col + ' .listFileSelect').append('<div class="clearfix list-group-item" filename="' + arr[i] + '">' +
                    '<img src="<?php echo IMAGESERVER?>resizepng-100x100/upload/' + app.entity.tablename + '/' + this.request.id + '/' + arr[i] + '"> ' + arr[i] + '<button type="button" onclick="app.removeAttachment($(this),\'' + col + '\')" class="btn btn-sm btn-danger float-right"><i class="fa fa-trash"></i></button></div>');
            }
        } catch (e) {

        }

    },
    updateAttachment: function (col, formid) {
        if (formid == "undefined") {
            formid = 'frm' + app.entity.classname;
        }
        let arr = new Array();
        for (var i = 0; i < $('#' + formid + ' #col_' + col + ' .listFileSelect').children().length; i++) {
            var e = $('#' + formid + ' #col_' + col + ' .listFileSelect').children()[i].getAttribute('basename');
            if (e == null) {
                e = $('#' + formid + ' #col_' + col + ' .listFileSelect').children()[i].getAttribute('filename');
            }
            arr.push(e);
        }
        $('#' + formid + ' #' + col).val(JSON.stringify(arr));
    },
    loadImageMulti: function (col) {
        var strAttachment = $('#frm' + app.entity.classname + ' #' + col).val();
        try {
            var arr = JSON.parse(strAttachment);
            for (var i = 0; i < arr.length; i++) {
                $('#frm' + app.entity.classname + ' #col_' + col + ' .listFileSelect').append('<div class="col-md-3 text-center" basename="' + arr[i].image + '">' +
                    '<img src="<?php echo IMAGESERVER?>autosize-0x200/upload/' + app.entity.tablename + '/' + this.request.id + '/' + arr[i].image + '">' +
                    '<input type="text" placeholder="Title" class="form-control imagetitle" value="' + arr[i].title + '">' +
                    '<textarea class="form-control imagesummary">' + arr[i].summary + '</textarea>' +
                    '<input type="text" placeholder="Link" class="form-control imagelink" value="' + arr[i].link + '">' +
                    '<button type="button" onclick="app.removeAttachment($(this),\'' + col + '\')" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button></div>');
            }
        } catch (e) {

        }


    },
    updateImageMulti: function (col) {
        let arr = new Array();
        for (var i = 0; i < $('#frm' + app.entity.classname + ' #col_' + col + ' .listFileSelect').children().length; i++) {
            var e = $('#frm' + app.entity.classname + ' #col_' + col + ' .listFileSelect').children()[i].getAttribute('basename');
            var title = $('#frm' + app.entity.classname + ' #col_' + col + ' .listFileSelect').children()[i].children[1].value;
            var summary = $('#frm' + app.entity.classname + ' #col_' + col + ' .listFileSelect').children()[i].children[2].value;
            var link = $('#frm' + app.entity.classname + ' #col_' + col + ' .listFileSelect').children()[i].children[3].value;
            arr.push({image: e, title: title, summary: summary, link: link});
        }
        $('#frm' + app.entity.classname + ' #' + col).val(JSON.stringify(arr));
    },
    removeAttachment: function (element, col, formid) {
        if (formid == "undefined") {
            formid = 'frm' + app.entity.classname;
        }
        element.parent().remove();
        app.updateAttachment(col, formid);
    },
    removeFile: function (element, col) {
        element.parent().remove();
        $('#frm' + app.entity.classname + ' #' + col).attr('data-value', '');
        $('#frm' + app.entity.classname + ' #' + col).val('');
    },
    saveForm: function () {
        app.saveFormInput(app.entity.entitytype, app.entity.classname, function (result) {
            //window.location = '?route=' + app.entity.entitytype + '/' + app.entity.classname + '/View&id=' + result.data.id
            if (app.recordData == null) {
                if (result.statuscode) {
                    //window.location = '?route=' + app.entity.entitytype + '/' + app.entity.classname + '/Edit&id=' + result.data.id;
                    $.getJSON('?route=Common/File/RenameDir&from=' + app.entity.tablename + '/' + app.dirtemp + '&to=' + app.entity.tablename + '/' + result.data.id, function (res) {
                        window.location = '?route=' + app.entity.entitytype + '/' + app.entity.classname;
                    })

                }
            } else {
                if (result.statuscode) {
                    if (app.isReload) {
                        window.location.reload();
                    }
                }
            }
        });
    },
    saveFormInputCustom: function (entitytype, classname,formid, callback) {
        common.showLoading();
        var obj = {};
        $(document).triggerAll('beforeSave');
        if ($('[datatype=code]').length) {
            $('[datatype=code]').val(app.editor.getValue());
        }
        if ($('.textarea_editor').length) {
            $('.textarea_editor').each(function () {
                var content = tinymce.get($(this).attr('id')).getBody().innerHTML;
                $(this).val(content);
            });
        }
        $('#' + formid + ' [datatype=keyvalue]').each(function () {
            var val = app.parseKeyValue($(this).attr('name'));
            console.log(val);
            $(this).val(val);
        });
        $('#' + formid + ' [datatype=imagemulti]').each(function () {
            app.updateImageMulti($(this).attr('id'));
        });
        $('#' + formid + ' [datatype=attachment]').each(function () {
            app.updateAttachment($(this).attr('id'), formid);
        });
        $('#' + formid + ' [datatype=relatedto]').each(function () {
            var val = $(this).val();
            if (val == null) {
                obj[$(this).attr('id')] = 0;
            }
        });
        $('#' + formid + ' [datatype=relatedtomulti]').each(function () {
            var arr = $(this).val()
            $('[name=' + $(this).attr('id') + ']').val(common.arrayToString(arr))
        });
        $('#' + formid + ' [datatype=optionsetmulti]').each(function () {
            var arr = $(this).val()
            $('[name=' + $(this).attr('id') + ']').val(common.arrayToString(arr))
        });

        var form = $('#' + formid)[0];

        var data = new FormData(form);
        for (const key in obj) {
            data.set(key, obj[key]);
        }

        $.ajax({
            type: "POST",
            enctype: 'multipart/form-data',
            url: "?route=" + entitytype + "/" + classname + "/Save",
            data: data,
            processData: false,
            contentType: false,
            cache: false,
            timeout: 600000,
            success: function (result) {
                common.endLoading();
                result = JSON.parse(result);
                $('.form-group').removeClass('has-danger');
                $('.form-control-feedback').html('');
                if (result.statuscode) {
                    toastr["success"](result.text, "");
                    $('.form-group small').remove();
                    app.isEditing = false;
                } else {
                    var arr = new Array();
                    for (var i in result.data) {
                        arr.push(result.data[i])
                        $('#frm' + classname + ' #col_' + i).addClass('has-danger');
                        $('#frm' + classname + ' #infor_' + i).html(result.data[i]);
                    }
                    toastr["error"](arr.join('<br>'), result.text);
                }
                $(document).triggerAll('afterSave');
                callback(result);
            },
            error: function (e) {
                common.endLoading();
                toastr["error"](e.responseText, "Error");
            }
        });
    },
    saveFormInput: function (entitytype, classname, callback) {
        common.showLoading();
        var obj = {};
        $(document).triggerAll('beforeSave');
        if ($('[datatype=code]').length) {
            $('[datatype=code]').val(app.editor.getValue());
        }
        if ($('.textarea_editor').length) {
            $('.textarea_editor').each(function () {
                var content = tinymce.get($(this).attr('id')).getBody().innerHTML;
                $(this).val(content);
            });
        }
        $('#frm' + classname + ' [datatype=keyvalue]').each(function () {
            var val = app.parseKeyValue($(this).attr('name'));
            console.log(val);
            $(this).val(val);
        });
        $('#frm' + classname + ' [datatype=imagemulti]').each(function () {
            app.updateImageMulti($(this).attr('id'));
        });
        $('#frm' + classname + ' [datatype=attachment]').each(function () {
            app.updateAttachment($(this).attr('id'), 'frm' + classname);
        });
        $('#frm' + classname + ' [datatype=relatedto]').each(function () {
            var val = $(this).val();
            if (val == null) {
                obj[$(this).attr('id')] = 0;
            }
        });
        $('#frm' + classname + ' [datatype=relatedtomulti]').each(function () {
            var arr = $(this).val()
            $('[name=' + $(this).attr('id') + ']').val(common.arrayToString(arr))
        });
        $('#frm' + classname + ' [datatype=optionsetmulti]').each(function () {
            var arr = $(this).val()
            $('[name=' + $(this).attr('id') + ']').val(common.arrayToString(arr))
        });

        var form = $('#frm' + classname)[0];

        var data = new FormData(form);
        for (const key in obj) {
            data.set(key, obj[key]);
        }

        $.ajax({
            type: "POST",
            enctype: 'multipart/form-data',
            url: "?route=" + entitytype + "/" + classname + "/Save",
            data: data,
            processData: false,
            contentType: false,
            cache: false,
            timeout: 600000,
            success: function (result) {
                common.endLoading();
                result = JSON.parse(result);
                $('.form-group').removeClass('has-danger');
                $('.form-control-feedback').html('');
                if (result.statuscode) {
                    toastr["success"](result.text, "");
                    $('.form-group small').remove();
                    app.isEditing = false;
                } else {
                    var arr = new Array();
                    for (var i in result.data) {
                        arr.push(result.data[i])
                        $('#frm' + classname + ' #col_' + i).addClass('has-danger');
                        $('#frm' + classname + ' #infor_' + i).html(result.data[i]);
                    }
                    toastr["error"](arr.join('<br>'), result.text);
                }
                $(document).triggerAll('afterSave');
                callback(result);
            },
            error: function (e) {
                common.endLoading();
                toastr["error"](e.responseText, "Error");
            }
        });
    },
    postData: function (url, data, doneFunc, failFunc) {
        $.ajax({
            type: "POST",
            url: url,
            data: data,
            success: function (result) {
                if (typeof result !== 'object') {
                    try {
                        result = JSON.parse(result);
                    } catch (e) {
                        console.log(e);
                    }

                }
                doneFunc(result);
            },
            error: function (e) {
                failFunc(e);
                common.endLoading();
            }
        });
    },
    postDataAwait: function (url, data) {
        var settings = {
            "url": url,
            "method": "POST",
            "timeout": 0,
            async: false,
            "data": data
        };

        var response = $.ajax(settings).responseText;
        if (response != '') {
            return JSON.parse(response);
        } else {
            return null
        }
    },
    getDataAwait: function (url, data) {
        var settings = {
            "url": url,
            "method": "GET",
            "timeout": 0,
            beforeSend: function () {

            },
            async: false,
            "data": data
        };
        var response = $.ajax(settings).responseText;
        return response;
    },
    getUrl: function () {
        var url = '?';
        var arr = new Array();
        for (var i in this.request) {
            arr.push(i + '=' + this.request[i]);
        }
        url += arr.join('&');
        return url;
    },
    goBack: function () {
        history.back()
        // if (window.history.length > 1) {
        //     window.location.href = document.referrer;
        // } else {
        //     window.location = "?route=" + this.entity.entitytype + "/" + this.entity.classname;
        // }
    },
    showChangePasswordForm: function (element) {
        var content = '<div class="form-group">' +
            '<label class="control-label">Mật khẩu mới</label>' +
            '<input type="password" id="txtPassword" class="form-control">' +
            '</div>';
        common.openModal('Đổi mật khẩu', content, 'Đổi', 'Hủy', element, function (e) {
            var data = {
                recordid: app.recordid,
                entityid: app.entity.id,
                colname: e.attr('id'),
                password: $('#txtPassword').val()
            };
            var result = app.postDataAwait('?route=Core/Entity/updatePassword', data);
            toastr["success"](result.text, "");
            common.closeModal();
        });
    },
    logout: function () {
        app.getDataAwait('?route=Core/Auth/logout');
        window.location.reload();
    },

    getEntityRelated: function (entityid) {
        $.getJSON("?route=Core/Entity/getEntityRelated&id=" + entityid, function (data) {
            if (data.statuscode == 1) {
                var entitys = data.data;
                for (var i in entitys) {
                    if (entitys[i].issystem == 0) {
                        var url = '?route=' + app.entity.entitytype + '/' + app.entity.classname + '/View&id=' + app.recordid + '&entityrelated=' + entitys[i].id;
                        $('.nav-tabs').append('<li class="nav-item"> <a class="nav-link" entityid="' + entitys[i].id + '" index="' + i + '" href="' + url + '"><span class="hidden-sm-up"><i class="ti-user"></i></span> <span class="hidden-xs-down">' + entitys[i]['entityname'] + '</span> <span class="badge badge-info">0</span></a> </li>');
                        $('.tab-content').append('<div class="tab-pane p-20 entityrelated" index="' + i + '" id="' + entitys[i]['classname'] + '" entityid="' + entitys[i].id + '" entitydata=\'' + JSON.stringify(entitys[i]) + '\' role="tabpanel"></div>');
                    }

                    //var table = app.loadDataRelate(entitys[i]);
                    //$('.tab-content #'+entitys[i]['classname']).html(table);
                }
                // $('.tab-content .entityrelated').each(function () {
                //     var index = $(this).attr('index');
                //     app.genRelatedView($(this),entitys[index],'');
                // });
                $('.nav-link .badge-info').each(function () {
                    var index = $(this).parent().attr('index');
                    var el = $(this)
                    app.countRelated(entitys[index], function (result) {
                        if (result.statuscode) {
                            el.html(result.count);
                        }
                    });
                });
                if (app.request.entityrelated != undefined) {
                    console.log(app.request.entityrelated);
                    var element = $('.tab-pane[entityid=' + app.request.entityrelated + ']');
                    var index = element.attr('index');
                    app.genRelatedView(element, entitys[index], '');
                    $('.tab-pane').removeClass('active');
                    element.addClass('active');
                    $('.nav-link').removeClass('active');
                    $('.nav-link[entityid=' + app.request.entityrelated + ']').addClass('active');
                    $('.maincol').click(function () {
                        var id = $(this).attr('itemid');
                        var entitytype = $(this).attr('entitytype');
                        var classname = $(this).attr('classname');
                        app.recordid = id;
                        //window.open("?route=" + entitytype + "/" + classname + "/View&id=" + id);
                        app.openRelateTab("?route=" + entitytype + "/" + classname + "/View&id=" + id)
                    });
                }
            }
        });
    },
    genRelatedView: function (tabcontentelement, entity, url) {
        var table = app.loadDataRelate(entity, url);
        var btnAddNew = '<button type="button" class="btn btn-success btnAddRelated" entityid="' + entity.id + '"><i class="fa fa-plus"></i> Thêm mới</button>';
        tabcontentelement.html(table + btnAddNew);
        var maincol = entity.maincol;
        console.log('maincol: ' + maincol);
        $('[attributeid=' + maincol + ']').addClass('maincol');
        $('[attributeid=' + maincol + ']').attr('entitytype', entity.entitytype);
        $('[attributeid=' + maincol + ']').attr('classname', entity.classname);
        $('.btnAddRelated').unbind('click');
        $('.btnAddRelated').click(function () {
            app.openQuickFrom($(this).attr('entityid'), undefined, function () {
                window.location.reload();
            });
        });
        $('.relatedpaging').unbind('click');
        $('.relatedpaging').click(function () {
            common.showLoading();
            console.log($(this).attr('ref'));
            console.log($(this).parent().parent().parent().parent().attr('id'));
            var element = $(this).parent().parent().parent().parent();
            var entity = JSON.parse(element.attr('entitydata'));
            var url = $(this).attr('ref');
            setTimeout(function () {

                app.genRelatedView(element, entity, url);
                common.endLoading();
                $('.maincol').click(function () {
                    var id = $(this).attr('itemid');
                    var entitytype = $(this).attr('entitytype');
                    var classname = $(this).attr('classname');
                    app.recordid = id;
                    //window.open("?route=" + entitytype + "/" + classname + "/View&id=" + id);
                    app.openRelateTab("?route=" + entitytype + "/" + classname + "/View&id=" + id)
                });
            }, 100);

        });
        $('[datatype=TEXT]').each(function () {
            var obj = $(this).html();
            try {
                obj = JSON.parse(obj);
                $(this).html(JSON.stringify(obj, undefined, 4));
            } catch (e) {

            }

        });
        $(document).triggerAll('genRelatedViewReady');
    },
    countRelated: function (entity, callback) {
        var condition = '';
        for (var i in entity.attributes) {
            if (entity.attributes[i].datatype = 'relatedto' && entity.attributes[i].entityrelated == app.entity.id) {
                condition = entity.attributes[i].attributename + '=equal_' + app.recordid;
            }
        }
        $.getJSON("?route=" + entity.entitytype + "/" + entity.classname + "/getCountItem&" + condition, function (result) {
            callback(result);
        });
    },
    loadDataRelate: function (entity, url) {
        var response = app.getDataAwait("?route=Core/EntityTemplate/getTemplates&entityid=" + entity['id'] + "&templatetype=view&isdefault=1");
        try {
            var result = JSON.parse(response);
            if (result.statuscode == 1) {
                var template = result.data[0];
                var view = JSON.parse(template.templatecontent);
                console.log(template.id);
                console.log(entity);
                var condition = '';
                for (var i in entity.attributes) {
                    if (entity.attributes[i].datatype = 'relatedto' && entity.attributes[i].entityrelated == app.entity.id) {
                        condition = entity.attributes[i].attributename + '=equal_' + app.recordid;
                    }
                }
                condition += '&paging=true&templateid=' + template.id;
                //condition += '&paging=true';
                console.log(condition);
                if (view.sort != undefined && view.sort.length > 0) {
                    for (const i in view.sort) {
                        console.log(view.sort[i]);
                    }
                    var sortcol = view.sort[0].attributename;
                    var sorttype = view.sort[0].sorttype;
                    condition += '&sortcol=' + sortcol + '&sorttype=' + sorttype;

                }
                if (url == '') {
                    var res = app.getDataAwait("?route=" + entity.entitytype + "/" + entity.classname + "/get" + entity.classname + "s&" + condition);
                } else {
                    var res = app.getDataAwait(url);
                }

                var result1 = JSON.parse(res);
                var data = result1.data;
                var cols = view.cols;
                console.log(cols);
                var header = '<tr>';
                for (var i in cols) {
                    header += '<th attributeid="' + cols[i].attributeid + '" attributename="' + cols[i].attributename + '" datatype="' + cols[i].datatype + '" datalength="' + cols[i].datalength + '" entityrelated="' + cols[i].entityrelated + '">' + cols[i].attributelabel + '</th>'
                }
                header += '</tr>';
                var body = '';
                for (var i in data) {
                    body += '<tr>'
                    for (var j in cols) {
                        //body += '<td datatype="'+cols[j].datatype+'" data-value="'+data[i][cols[j].attributename]+'">'+data[i][cols[j].attributename]+'</td>';
                        var value = data[i][cols[j].attributename] != undefined ? data[i][cols[j].attributename] : data[i][cols[j].attributeid];
                        body += app.genCellView(data[i].id, value, cols[j].attributeid, cols[j].attributename, cols[j].datatype, cols[j].entityrelated, cols[j].optionsetid);
                    }
                    body += '</tr>';
                }
                var table = '<table class="table table-bordered table-striped dataTable no-footer" cellspacing="0" width="100%">' +
                    '<thead>' + header + '</thead>' +
                    '<tbody>' + body + '</tbody>' +
                    '</table>';
                console.log(result);
                table += '<nav>' + result1.paginationajax + '</nav>';
                return table;
            }
        } catch (e) {

        }
    },
    viewLog: function () {
        $('#modalQuickView').modal('hide');
        $('#modalViewLog').modal();
        $('#modalViewLog .modal-body').load('?route=Core/Entity/ViewLog&entityid=' + app.entity.id + '&recordid=' + app.recordid);
    },
    genMappingForm: function () {
        var html = '';
        for (var i in app.entity.attributes) {
            html += '<div class="col-6">' + app.entity.attributes[i]['attributelabel'] + (app.entity.attributes[i]['isrequire'] == 1 ? '(*)' : '') + '</div>';
            html += '<div class="col-6"><select class="form-control selMapping" index="' + i + '" attributeid="' + app.entity.attributes[i]['id'] + '" attributename="' + app.entity.attributes[i]['attributename'] + '" attributelabel="' + app.entity.attributes[i]['attributelabel'] + '" isrequire="' + app.entity.attributes[i]['isrequire'] + '"></select></div>';
        }
        html += '<div class="col-6 hide">ID</div>';
        html += '<div class="col-6 hide"><select class="form-control selMapping" index="' + (i + 1) + '" attributeid="id" attributename="id" attributelabel="ID"></select></div>';
        return '<div class="row">' + html + '</div>';
    },
    exportData: function () {
        common.showLoading();
        this.postData("?route=" + this.entity.entitytype + "/" + this.entity.classname + "/Export", this.request, function (result) {
            window.location = result.link;
            common.endLoading()
        }, function () {
            common.endLoading()
        })

    },
    importItem: function (index) {
        if ($('#frmImportItem-' + index).length > 0) {
            app.postData("?route=" + app.entity.entitytype + "/" + app.entity.classname + "/Import", $('#frmImportItem-' + index).serialize(), function (result) {
                console.log(result);
                var warring = '';
                var text = '';
                if (result.statuscode) {
                    //warring = '<li class="list-group-item bg-success text-white">' + result.text + '</li>';
                    warring = 'bg-success';
                    text = result.text;
                } else {
                    var arr = new Array();
                    for (var i in result.data) {
                        arr.push(result.data[i])
                    }
                    //toastr["error"](arr.join('<br>'), result.text);
                    // warring = '<li class="list-group-item bg-danger text-white">' + arr.join('<br>') + '</li>';
                    warring = 'bg-danger';
                    text = arr.join(' - ');
                }
                $("#frmImportItem-" + index + " #dropdownMenuButton").addClass(warring);
                $("#frmImportItem-" + index + " .dropdown-menu").prepend('<a class="dropdown-item ' + warring + '">' + text + '</a>');
                if($("#frmImportItem-" + index).offset().top < 200){
                    $('#modalImportForm').animate({
                        scrollTop: $("#frmImportItem-" + index).offset().top - $('#modalImportForm .modal-content').offset().top
                    }, 50, function () {
                        app.importItem(index + 1);
                    });
                }else {
                    app.importItem(index + 1);
                }

            });
        } else {
            if (index > 2) {
                toastr["success"]("Nhâp từ file thàng công", "");
                $('#btnImportFormSave').prop('disabled', true);
            } else {
                toastr["error"]("Bạn chưa chọn file", "");
            }

        }

    },
    createNotify: function (title, summary, url, callback) {
        var img = 'resource/assets/images/logo-light-icon.png';
        var notification = new Notification(title, {body: summary, icon: img, badge: url});
        notification.onclick = function (event) {
            callback(event);
        }
    },
    notifyMe: function (title, summary, url, callback) {
        if (!("Notification" in window)) {
            alert("This browser does not support desktop notification");
        } else if (Notification.permission === "granted") {
            // If it's okay let's create a notification
            app.createNotify(title, summary, url, callback)
        } else if (Notification.permission !== "denied") {
            Notification.requestPermission().then(function (permission) {
                // If the user accepts, let's create a notification
                if (permission === "granted") {
                    app.createNotify(title, summary, url, callback)
                }
            });
        }

        // At last, if the user has denied notifications, and you
        // want to be respectful there is no need to bother them any more.
    },
    loadNotifications: function () {
        $.getJSON('?route=Core/Notification/getNotifications&sortcol=createdat&sorttype=desc&paging=true&status=equal_new', function (result) {
            if (result.statuscode) {
                var str = '';
                for (const i in result.data) {
                    str += '<a href="#" onclick="app.viewNotification(' + result.data[i].id + ')">' +
                        '   <div class="btn btn-danger btn-circle"><i class="fa fa-link"></i></div>' +
                        '   <div class="mail-contnet" ' + (result.data[i].status == 'new' ? 'style="font-weight: bold"' : '') + '>' +
                        '       <h5>' + result.data[i].title + '</h5> <span class="mail-desc">' + result.data[i].summary + '</span> <span class="time">' + result.data[i].createdat + '</span> </div>' +
                        '</a>';
                    if (result.data[i].status == 'new') {
                        app.notifyMe(result.data[i].title, result.data[i].summary, '?route=Core/Notification/View&id=' + result.data[i].id, function () {
                            app.viewNotification(result.data[i].id);
                        });
                    }
                }
                $('#headernotifications').html(str);
            }
        })
    },
    viewNotification: function (id) {
        app.postData('?route=Core/Notification/Save', {
            id: id,
            status: 'viewed',
            viewby: app.user.id,
            viewat: common.dateTimeShow(new Date(), 'YMD'),
        }, function () {
            window.location = '?route=Core/Notification/View&id=' + id;
        })
    },
    loadMessage: function () {
        $.getJSON('?route=Core/Message/getMessages&isread=equal_0&sortcol=createdat&sorttype=desc', function (result) {
            if (result.statuscode) {
                var str = '';
                for (const i in result.data) {
                    str += '<a href="#" onclick="app.viewMessage(' + result.data[i].id + ')">' +
                        '   <div class="btn btn-danger btn-circle"><i class="fa fa-link"></i></div>' +
                        '   <div class="mail-contnet">' +
                        '       <h5>' + result.data[i].fullname + '</h5> <span class="mail-desc">' + result.data[i].title + '</span> <span class="time">' + result.data[i].createdat + '</span> ' +
                        '</div>' +
                        '</a>';
                }
                $('#headermessage').html(str);
                $('#heardermessagecount').html(result.data.length);
            }
        })
    },
    viewMessage: function (id) {
        app.postData('?route=Core/Message/Save', {
            id: id,
            isread: 1,
            viewby: app.user.id,
            viewat: common.dateTimeShow(new Date(), 'YMD'),
        }, function () {
            window.location = '?route=Core/Message/View&id=' + id;
        })
    }
}

$(document).ready(function () {
    //app.loadNotifications();
    //app.loadMessage();
    switch (app.method) {
        case "Insert":
        case "Edit":
            app.loadFormData();
            $('[datatype=PASSWORD]').click(function () {
                app.showChangePasswordForm($(this));
            });
            $(document).bind('keydown', function (e) {
                if (e.ctrlKey && (e.which == 83)) {
                    e.preventDefault();
                    app.saveForm()
                    return false;
                }
            });
            break;
        case "View":
            app.recordid = app.request.id;
            var result = app.getDataAwait("?route=" + app.entity.entitytype + "/" + app.entity.classname + "/get" + app.entity.classname + "&id=" + app.recordid);
            result = JSON.parse(result);
            if (result.statuscode) {
                app.recordData = result.data;
                $('title').html(app.recordData[app.entity.mainattribute.attributename]);
                app.getEntityRelated(app.entity.id);
                $('.hlcontrol').each(function () {
                    if ($(this).attr('control') == 'tableview') {
                        $('#' + $(this).attr('id') + ' table').append('<tbody></tbody>');
                        //Load recordData

                        app.loadDataTableView($(this), false);
                    }
                });
                $('[datatype=attachment]').each(function () {
                    try {
                        var obj = JSON.parse($(this).html())
                        $(this).html(obj.join(' - '));
                    } catch (e) {

                    }
                });
                $('[datatype=imagemulti]').each(function () {
                    try {
                        var obj = JSON.parse($(this).html())
                        var html = '';
                        for (var i in obj) {
                            html += '<div class="col-md-3">' +
                                '<img src="<?php echo IMAGESERVER?>autosize-0x200/upload/' + app.entity.tablename + '/' + app.recordid + '/' + obj[i].image + '">' +
                                '<div>' + obj[i].title + '</div>' +
                                '</div>';
                        }
                        html = '<div class="row text-center">' + html + '</div>';
                        $(this).html(html);
                    } catch (e) {

                    }
                });
                $(document).triggerAll('loadViewReady');
            } else {
                window.location = HTTPSERVER;
            }

            break;
        case "List":
            if (app.entity.id != undefined) {
                app.loadAtributeCodition();
                $('.maincol').click(function () {
                    var id = $(this).attr('itemid');
                    app.recordid = id;
                    //window.open("?route=" + app.entity.entitytype + "/" + app.entity.classname + "/View&id=" + id);
                    app.openRelateTab("?route=" + entitytype + "/" + classname + "/View&id=" + id);
                    /*$('#modalQuickView').modal();
                    $('#modalQuickView .modal-title').html(name);
                    $('#modalQuickView .modal-body').load("?route="+app.entity.entitytype+"/"+app.entity.classname+"/View&id="+id,function () {
                        $('.hlcontrol').each(function () {
                            if($(this).attr('control')=='tableview'){
                                $('.btnTableViewAddRow').click(function () {
                                    app.openQuickFrom($(this).parent().attr('entityid'));
                                });
                                $('#'+$(this).attr('id') + ' table').append('<tbody></tbody>');
                                //Load recordData
                                var result = app.getDataAwait("?route="+app.entity.entitytype+"/"+app.entity.classname+"/get"+app.entity.classname+"&id="+id);
                                result = JSON.parse(result);
                                app.recordData = result.data;
                                app.loadDataTableView($(this),false);
                            }
                        });
                        $('#modalQuickView [datatype=attachment]').each(function () {
                            try{
                                var obj = JSON.parse($(this).html())
                                $(this).html(obj.join(' - '));
                            }catch (e) {

                            }
                        });
                        $('#modalQuickView [datatype=imagemulti]').each(function () {
                            try{
                                var obj = JSON.parse($(this).html())
                                var html = '';
                                for (var i in obj){
                                    html += '<div class="col-md-3">' +
   '<img src="<?php echo IMAGESERVER?>autosize-0x200/upload/'+app.entity.tablename+'/'+app.recordid+'/'+obj[i].image+'">' +
                                    '<div>'+obj[i].title+'</div>' +
                                    '</div>';
                                }
                                html = '<div class="row text-center">'+html+'</div>';
                                $(this).html(html);
                            }catch (e) {

                            }
                        });
                    });*/
                });
                if (app.permission.Edit == 0) {
                    $('#btnQuickViewEdit').remove();
                }
                if (app.permission.Delete == 0) {
                    $('#btnQuickViewDel').remove();
                }
                $('#btnQuickViewEdit').click(function () {
                    app.recordid = $('#modalQuickView #id').val();
                    window.location = "?route=" + app.entity.entitytype + "/" + app.entity.classname + "/Edit&id=" + app.recordid;
                });
                $('#btnQuickViewLog').click(function () {
                    $('#modalQuickView').modal('hide');
                    $('#modalViewLog').modal();
                    $('#modalViewLog .modal-body').load('?route=Core/Entity/ViewLog&entityid=' + app.entity.id + '&recordid=' + app.recordid);
                });
                $('#btnQuickViewDel').click(function () {
                    var id = $('#modalQuickView #id').val();
                    var name = $('#modalQuickView .modal-title').html();
                    $.confirm({
                        title: 'Confirm!',
                        content: 'Do you want to delete ' + name + '?',
                        buttons: {
                            yes: {
                                text: 'Yes', // text for button
                                btnClass: 'btn-red', // class for the button
                                keys: ['enter', 'y'], // keyboard event for button
                                isHidden: false, // initially not hidden
                                isDisabled: false, // initially not disabled
                                action: function () {
                                    $.getJSON("?route=" + app.entity.entitytype + "/" + app.entity.classname + "/Delete&id=" + id, function (result) {
                                        if (result.statuscode == 1) {
                                            window.location.reload();
                                        } else {
                                            toastr["error"](e.responseText, "Error");
                                        }
                                    })
                                }
                            },
                            no: function () {

                            },
                        }
                    });
                });
                $("th[datatype]").each(function () {
                    if (app.request.sortcol == $(this).attr('col')) {
                        if (app.request.sorttype == 'asc') {
                            $(this).addClass('sorting_asc');
                        } else {
                            $(this).addClass('sorting_desc');
                        }
                    } else {
                        $(this).addClass('sorting');
                    }

                });

                $('.sorting').click(function () {
                    app.request.sortcol = $(this).attr('col');
                    app.request.sorttype = 'asc';
                    var url = app.getUrl();
                    window.location = url;
                });
                $('.sorting_asc').click(function () {
                    app.request.sortcol = $(this).attr('col');
                    app.request.sorttype = 'desc';
                    var url = app.getUrl();
                    window.location = url;
                });
                $('.sorting_desc').click(function () {
                    app.request.sortcol = $(this).attr('col');
                    app.request.sorttype = 'asc';
                    var url = app.getUrl();
                    window.location = url;
                });
                $('#listOperator').hide();
                $('#listAttribute').change(function () {
                    if ($(this).val() == '') {
                        $('#listOperator').hide();
                    } else {
                        $('#listOperator').show();
                    }
                    var col = $(this).val();
                    var datatype = $('#listAttribute option[value=' + col + ']').attr('datatype');
                    var entityrelated = Number($('#listAttribute option[value=' + col + ']').attr('entityrelated'));
                    var optionsetid = Number($('#listAttribute option[value=' + col + ']').attr('optionsetid'));
                    var attributeid = Number($('#listAttribute option[value=' + col + ']').attr('attributeid'));
                    var str = '';
                    for (var i in app.dataoperator[datatype]) {
                        str += '<option value="' + app.dataoperator[datatype][i] + '">' + app.operatorchar[app.dataoperator[datatype][i]] + '</option>';
                    }
                    $('#listOperator').html(str);
                    $('.seachvalue').addClass('hide')
                    switch (datatype) {
                        case 'VARCHAR':
                        case 'TEXT':
                        case 'LONGTEXT':
                            $('#searchtext').removeClass('hide');
                            app.searchcurent = 'searchtextinput';
                            break;
                        case 'INT':
                        case 'FLOAT':
                        case 'BIGINT':
                        case 'DOUBLE':
                            $('#searchnumber').removeClass('hide');
                            app.searchcurent = 'searchnumberinput';
                            break;
                        case 'DATE':
                        case 'DATETIME':
                            $('#searchdate').removeClass('hide');
                            app.searchcurent = 'searchdateinput';
                            break;
                        case 'TIME':
                            $('#searchtime').removeClass('hide');
                            app.searchcurent = 'searchtimeinput';
                            break;
                        case 'relatedto':
                        case 'relatedtomulti':
                            $('#searchrelated').removeClass('hide');
                            app.searchcurent = 'searchrelatedinput';
                            break;
                        case 'optionset':
                        case 'optionsetmulti':
                            $('#searchoptionset').removeClass('hide');
                            app.searchcurent = 'searchoptionsetinput';
                            break;
                        case 'BOOLEAN':
                            $('#searchbool').removeClass('hide');
                            app.searchcurent = 'searchboolinput';
                            break;
                    }
                    var element = $('#' + app.searchcurent);
                    if (entityrelated > 0) {
                        app.genRelateControl2(element, entityrelated);
                    }
                    if (datatype == 'optionset' || datatype == 'optionsetmulti') {
                        element.attr('searchname', $('#listAttribute option[value=' + col + ']').val());
                        if (optionsetid) {
                            app.genOptionSetControl(element, optionsetid);
                        } else {
                            var data = null;
                            for (const i in app.entity.attributes) {
                                if (app.entity.attributes[i].attributename == col) {
                                    data = app.entity.attributes[i].optionsetvalue;
                                }
                            }
                            app.genOptionSetControlData(element, data);
                        }

                    }
                });
                $('#listOperator').change(function () {
                    var entityrelated = Number($('#listAttribute option[value=' + $('#listAttribute').val() + ']').attr('entityrelated'));
                    var attributename = $('#listAttribute').val();
                    var datatype = $('#listAttribute option[value=' + attributename + ']').attr('datatype');
                    var optionsetid = Number($('#listAttribute option[value=' + $('#listAttribute').val() + ']').attr('optionsetid'));
                    var operator = $(this).val();
                    var element = $('#' + app.searchcurent);
                    switch (datatype) {
                        case 'optionset':
                        case 'optionsetmulti':
                            element.attr('searchname', $('#listAttribute option[value=' + $('#listAttribute').val() + ']').val());
                            if ($('#listOperator').val() == 'in' || $('#listOperator').val() == 'notin') {
                                element.prop('multiple', true);
                            } else {
                                element.prop('multiple', false);
                            }
                            app.genOptionSetControl(element, optionsetid);
                            element.selectpicker('destroy');
                            element.selectpicker();
                            break;
                        case 'relatedto':
                        case 'relatedtomulti':
                            if ($('#listOperator').val() == 'in' || $('#listOperator').val() == 'notin') {
                                element.prop('multiple', true);
                            } else {
                                element.prop('multiple', false);
                            }
                            app.genRelateControl2(element, entityrelated);
                            break;
                        case 'INT':
                        case 'FLOAT':
                        case 'BIGINT':
                        case 'DOUBLE':
                            $('.seachvalue').addClass('hide');
                            if (operator == 'between' || operator == 'notbetween') {
                                $('#searchnumberrange').removeClass('hide');
                                app.searchcurent = 'searchnumberrangeinput';
                            } else {
                                $('#searchnumber').removeClass('hide');
                                app.searchcurent = 'searchnumberinput';
                            }
                            break;
                        case 'DATE':
                        case 'DATETIME':
                            $('.seachvalue').addClass('hide');
                            if (operator == 'between' || operator == 'notbetween') {
                                $('#searchdaterange').removeClass('hide');
                                app.searchcurent = 'searchdaterangeinput';
                            } else {
                                $('#searchdate').removeClass('hide');
                                app.searchcurent = 'searchdateinput';
                            }
                            break;
                    }
                    /*var col = $('#listAttribute').val();
                    var entityrelated = Number($('#listAttribute option[value=' + col + ']').attr('entityrelated'));
                    var datatype = $('#listAttribute option[value=' + col + ']').attr('datatype');
                    var optionsetid = Number($('#listAttribute option[value=' + col + ']').attr('optionsetid'));
                    var operator = $(this).val();
                    $('.seachvalue').addClass('hide')
                    switch (operator) {
                        case 'equal':
                        case 'notequal':
                            switch (datatype) {
                                case 'relatedto':
                                    $('#searchrelated').removeClass('hide');
                                    app.searchcurent = 'searchrelatedinput';
                                    break;
                                case 'VARCHAR':
                                    $('#searchtext').removeClass('hide');
                                    app.searchcurent = 'searchdateinput';
                                case 'DATETIME':
                                    $('#searchdate').removeClass('hide');
                                    app.searchcurent = 'searchtextinput';
                            }
                            break;
                        case 'contains':
                        case 'notcontains':
                            switch (datatype) {
                                case 'VARCHAR':
                                    $('#searchtext').removeClass('hide');
                                    app.searchcurent = 'searchtextinput';
                            }
                            break;
                        case 'in':
                        case 'notin':
                            $('#searchrelatedmulti').removeClass('hide');
                            app.searchcurent = 'searchrelatedinputmulti';
                            break;
                    }
                    */
                });
                $('#btnModalAddCondition').click(function () {
                    var col = $('#listAttribute').val();
                    var datatype = $('#listAttribute option[value=' + col + ']').attr('datatype');
                    var operator = $('#listOperator').val();
                    var val = '';
                    switch (operator) {
                        case 'in':
                        case 'notin':
                            var arr = Array.isArray($('#' + app.searchcurent).val()) ? $('#' + app.searchcurent).val() : $('#' + app.searchcurent).val().split(',');
                            var val = arr.join('-');
                            break;
                        case 'between':
                        case 'notbetween':
                            switch (datatype) {
                                case 'INT':
                                case 'BIGINT':
                                case 'FLOAT':
                                case 'DOUBLE':
                                    var numberfrom = common.stringtoNumber($('#numberfrom').val());
                                    var numberto = common.stringtoNumber($('#numberto').val());
                                    $('#' + app.searchcurent).attr('data-value', '[' + numberfrom + '],[' + numberto + ']');
                                    break;
                            }
                            val = encodeURI($('#' + app.searchcurent).attr('data-value'));
                            break;
                        default:
                            val = $('#' + app.searchcurent).val();
                    }

                    if ($('#' + app.searchcurent).attr('type') == 'checkbox') {
                        val = $('#' + app.searchcurent).prop("checked") ? 1 : 0;
                    }
                    app.request[col] = operator + '_' + val;
                    var url = app.getUrl();
                    window.location = url;
                });
                var allAtribute = new Array();
                for (const i in app.entity.attributes) {
                    allAtribute.push(app.entity.attributes[i])
                }
                if (app.entity.coreattributes != undefined) {
                    allAtribute = allAtribute.concat(app.entity.coreattributes);

                }
                for (var i in allAtribute) {
                    var col = allAtribute[i].attributename;
                    if (app.request[col] != undefined) {
                        var entityrelated = allAtribute[i].entityrelated;
                        var optionsetid = allAtribute[i].optionsetid;
                        var datatype = allAtribute[i].datatype;
                        var collabel = allAtribute[i].attributelabel;
                        var arr = app.request[col].split('_')
                        var operator = arr[0];
                        var operator_text = app.operatorchar[arr[0]];
                        var val = arr[1];
                        var result = new Array();
                        switch (datatype) {
                            case 'relatedto':
                            case 'relatedtomulti':
                                var arr_val = val.split('-');
                                for (const i in arr_val) {
                                    var str = app.getDataAwait("?route=" + app.entity.entitytype + "/" + app.entity.classname + "/getReqRelatedValue", {
                                        valueid: arr_val[i],
                                        entityrelated: entityrelated
                                    });
                                    result.push(str);
                                }
                                break;
                            case 'optionset':
                            case 'optionsetmulti':
                                var arr_val = val.split('-');
                                for (const j in arr_val) {
                                    var str = app.getDataAwait("?route=" + app.entity.entitytype + "/" + app.entity.classname + "/getReqOptionSetValue", {
                                        key: arr_val[j],
                                        optionsetid: optionsetid,
                                        attributeid: allAtribute[i].id,
                                    });
                                    result.push(str);
                                }
                                break;
                            case 'DATE':
                            case 'DATETIME':
                                if (operator == 'between' || operator == 'notbetween') {
                                    var arr = common.stringToArray(val);
                                    result.push(common.dateShow(arr[0]) + ' - ' + common.dateShow(arr[1]));
                                } else {
                                    result.push(common.dateShow(val));
                                }

                                break;
                            default:
                                result.push(val);
                        }
                        $('#searchView').append('<div class="cols float-left">' +
                            collabel + ' ' + operator_text + ' ' + result.join(', ') +
                            '<button type="button" class="close searchRemove" colsearch="' + col + '" aria-label="Close"> <span aria-hidden="true">×</span></button></div>')
                    }
                }
                $('.searchRemove').click(function () {
                    var colsearch = $(this).attr('colsearch');
                    delete (app.request[colsearch]);
                    var url = app.getUrl();
                    window.location = url;
                });
                $('#btnImportFormSave').click(function () {
                    //Validate Import
                    var isrequire = true;
                    $('.selMapping[isrequire=1]').each(function () {
                        console.log($(this).val())
                        if ($(this).val() == '') {
                            isrequire = false;
                        }
                    });
                    if (!isrequire) {
                        toastr["error"]('You must select attribute require!', 'Error');
                    } else {
                        console.log('Import data');
                        //common.showLoading();
                        $('#importResult').html('');
                        for (var i in app.importData) {
                            if (i >= 2) {
                                var data = {};
                                var items = ''
                                var arrheader = new Array();
                                $('.selMapping').each(function () {
                                    if ($(this).val() != '') {

                                        var value = app.importData[i][$(this).val()];
                                        if ($(this).attr('isrequire') == '1') {
                                            arrheader.push($(this).attr('attributelabel') + ': ' + value);
                                        }
                                        var item = {
                                            lable: $(this).attr('attributelabel'),
                                            value: value
                                        };
                                        data[$(this).attr('attributename')] = item;
                                        if (value != null) {
                                            items += ' <a class="dropdown-item">' + $(this).attr('attributelabel') + ': ' + value +
                                                '<input type="hidden" name="' + $(this).attr('attributename') + '" value="' + value + '">' +
                                                '</a>';
                                        }

                                    }

                                });
                                items = '<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">' + items + '</div>';
                                $('#importResult').append('<form id="frmImportItem-' + i + '" class="frmImportItem">' +
                                    '<div class="dropdown">' +
                                    '  <button class="btn btn-secondary dropdown-toggle" style="width: 100%" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' +
                                    arrheader.join(' | ') +
                                    '  </button>' + items +
                                    '</div>' +
                                    '</form>');
                                console.log(data);
                            }
                        }
                        app.importItem(2);
                        /*setTimeout(function() {
                            for (var i in app.importData) {
                                if (i >= 2) {
                                    var data = {};
                                    var items = ''
                                    $('.selMapping').each(function() {
   if ($(this).val() != '') {
       var value = app.importData[i][$(this).val()];
       data[$(this).attr('attributename')] = value;
       if(value != null){
           items += ' <li class="list-group-item">'+$(this).attr('attributelabel')+': '+value+'</li>';
       }

   }
                                    });

                                    console.log(data);
                                    var result = app.postDataAwait("?route=" + app.entity.entitytype + "/" + app.entity.classname + "/Import", data);
                                    if(result.statuscode){
   //toastr["success"](result.text, "Success");
   items += '<li class="list-group-item bg-success">'+result.text+'</li>';
                                    }else {
   var arr = new Array();
   for (var i in result.data) {
       arr.push(result.data[i])
       $('#frm' + app.entity.classname + ' #col_' + i).addClass('has-danger');
       $('#frm' + app.entity.classname + ' #col_' + i).append('<small class="form-control-feedback"> ' + result.data[i] + '</small>');
   }
   //toastr["error"](arr.join('<br>'), result.text);
   items += '<li class="list-group-item bg-danger">'+arr.join('<br>')+'</li>';
                                    }
                                    $('#importResult').append('<ul class="list-group">'+items+'</ul>');
                                    $('#countpercent').html(i);
                                }
                            }
                            toastr["success"]("Import Completed!", "Success");
                            console.log('Import Completed!');
                            //$('#modalImportForm').modal('hide');
                            common.endLoading();
                            //window.location.reload();
                        }, 1000);*/


                    }
                });
            }
            $('[datatype=TEXT]').each(function () {
                var obj = $(this).html();
                try {
                    obj = JSON.parse(obj);
                    $(this).html(JSON.stringify(obj, undefined, 4));
                } catch (e) {

                }

            });
            $(document).triggerAll('loadListReady');
            break;
    }
});
(function ($) {
    $.fn.extend({
        triggerAll: function (events, params) {
            var el = this, i, evts = events.split(' ');
            for (i = 0; i < evts.length; i += 1) {
                el.trigger(evts[i], params);
            }
            return el;
        }
    });
})(jQuery);
