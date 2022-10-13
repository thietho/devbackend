var tree = {
    maincol: '',
    parentcol: '',
    sortordercol: '',
    data: '',
    listcol: [],
    inti: function() {
        var mainattribute = app.getAttribute(app.entity.maincol);
        this.maincol = mainattribute.attributename;
        var parentattribute = app.getAttribute(app.entity.parentcol)
        this.parentcol = parentattribute.attributename;
        var sortattribute = app.getAttribute(app.entity.sortcol)
        this.sortordercol = sortattribute.attributename;
        this.getView();
        console.log(this.listcol.cols);
    },
    show: function(element, rootid) {
        app.loadListbyParent1(rootid, this.sortordercol, function(result) {
            if (result.statuscode == 1) {
                if (result.data.length) {
                    var html = '<ol class="dd-list" data-id="' + rootid + '">';
                    for (var i in result.data) {
                        var display = result.data[i][tree.maincol];
                        if (tree.listcol.cols.length) {
                            var arr = new Array();
                            for (var j in tree.listcol.cols) {
                                var val = result.data[i][tree.listcol.cols[j].attributename];
                                if(result.data[i][tree.listcol.cols[j].attributename+'_text'] != undefined){
                                    val = result.data[i][tree.listcol.cols[j].attributename+'_text'];
                                }
                                arr.push(val);
                            }
                            display = arr.join(' - ');
                        }
                        html += '<li class="dd-item dd3-item" data-id="' + result.data[i].id + '">\n' +
                            '                    <div class="dd-handle dd3-handle"></div>\n' +
                            '                    <div class="dd3-content clearfix"> ' + display + ' ' +
                            '<button type="button" class="btn btn-success btn-sm float-right btntree btnAddChild" itemid="' + result.data[i].id + '" itemname="' + result.data[i][tree.maincol] + '"><i class="fa fa-plus"></i></button>' +
                            '<button type="button" class="btn btn-success btn-sm float-right btntree btnEdit" itemid="' + result.data[i].id + '" itemname="' + result.data[i][tree.maincol] + '"><i class="fa fa-pencil"></i></button>' +
                            '<button type="button" class="btn btn-danger btn-sm float-right btntree btnDel" itemid="' + result.data[i].id + '" itemname="' + result.data[i][tree.maincol] + '"><i class="fa fa-trash"></i></button>' +
                            '</div>\n' +
                            '                </li>';
                    }
                    html += '</ol>';
                    element.append(html);
                    for (var j = 0; j < $('ol[data-id=' + rootid + ']').children().length; j++) {
                        var id = $('ol[data-id=' + rootid + ']').children()[j].attributes['data-id'].value;
                        tree.show($('li[data-id=' + id + ']'), id);
                    }

                    $('.btnAddChild').unbind('click');
                    $('.btnAddChild').click(function() {
                        var id = $(this).attr('itemid');
                        if(typeof (window["addTreeChild"]) === "function"){
                            addTreeChild(id);
                        }else {
                            app.showQuickInsertForm(function () {
                                app.ajaxComplete(function () {
                                    console.log(id)
                                    app.itemReady($('#frmQuick #'+tree.parentcol),id);
                                })

                            });
                        }

                    });
                    $('.btnEdit').unbind('click');
                    $('.btnEdit').click(function() {
                        var id = $(this).attr('itemid')
                        window.location = "?route=" + app.entity.entitytype + "/" + app.entity.classname + "/Edit&id=" + id;
                    });
                    $('.btnDel').unbind('click');
                    $('.btnDel').click(function() {
                        var id = $(this).attr('itemid');
                        var name = $(this).attr('itemname');
                        $.confirm({
                            title: 'Confirm!',
                            content: 'Do you want to delete ' + name + '?',
                            buttons: {
                                yes: {
                                    text: 'Yes', // text for button
                                    btnClass: 'btn-red',
                                    keys: ['enter', 'y'],
                                    isHidden: false, //
                                    isDisabled: false,
                                    action: function() {
                                        $.getJSON("?route=" + app.entity.entitytype + "/" + app.entity.classname + "/Delete&id=" + id, function(result) {
                                            if (result.statuscode == 1) {
                                                window.location.reload();
                                            } else {
                                                toastr["error"](result.text, "Error");
                                            }
                                        })
                                    }
                                },
                                no: function() {

                                },
                            }
                        });
                    })
                }

            }
        });

    },
    getView: function() {
        var result = app.getDataAwait('?route=Core/EntityTemplate/getTemplates&templatetype=view&entityid=' + app.entity.id);
        try {
            result = JSON.parse(result);
            this.listcol = JSON.parse(result.data[0].templatecontent);
        } catch (e) {

        }
    },
    updateOutput: function(e) {
        var list = e.length ? e : $(e.target);
        if (window.JSON) {
            tree.data = JSON.stringify(list.nestable('serialize'));
            console.log(tree.data.cols);
        } else {
            output.val('JSON browser support required for this demo.');
        }
    },
    updateTree: function() {
        console.log(this.data);
        var result = app.postDataAwait("?route=" + app.entity.entitytype + "/" + app.entity.classname + "/updateTree", {
            data: this.data,
            maincol: this.maincol,
            parentcol: this.parentcol,
            sortordercol: this.sortordercol
        });
        toastr["success"]('Success', result.text);
        console.log(result);
    }
};
tree.inti();
console.log(tree);
tree.show($('#treeview'), 0);
$('#treeview').nestable({
    group: 1
}).on('change', tree.updateOutput);