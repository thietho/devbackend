common = {
    escapeUnicode: function (str) {
        str = str.toLowerCase();
        str = str.replace(/Ã |Ã¡|áº¡|áº£|Ã£|Ã¢|áº§|áº¥|áº­|áº©|áº«|Äƒ|áº±|áº¯|áº·|áº³|áºµ/g, "a");
        str = str.replace(/Ã¨|Ã©|áº¹|áº»|áº½|Ãª|á»�|áº¿|á»‡|á»ƒ|á»…/g, "e");
        str = str.replace(/Ã¬|Ã­|á»‹|á»‰|Ä©/g, "i");
        str = str.replace(/Ã²|Ã³|á»�|á»�|Ãµ|Ã´|á»“|á»‘|á»™|á»•|á»—|Æ¡|á»�|á»›|á»£|á»Ÿ|á»¡/g, "o");
        str = str.replace(/Ã¹|Ãº|á»¥|á»§|Å©|Æ°|á»«|á»©|á»±|á»­|á»¯/g, "u");
        str = str.replace(/á»³|Ã½|á»µ|á»·|á»¹/g, "y");
        str = str.replace(/Ä‘/g, "d");
        str = str.replace(/!|@|%|\^|\*|\(|\)|\+|\=|\<|\>|\?|\/|,|\.|\:|\;|\'| |\"|\&|\#|\[|\]|~|$|_/g, "-");
        /* tÃ¬m vÃ  thay tháº¿ cÃ¡c kÃ­ tá»± Ä‘áº·c biá»‡t trong chuá»—i sang kÃ­ tá»± - */
        str = str.replace(/-+-/g, "-"); //thay tháº¿ 2- thÃ nh 1-
        str = str.replace(/^\-+|\-+$/g, "");
        //cáº¯t bá»� kÃ½ tá»± - á»Ÿ Ä‘áº§u vÃ  cuá»‘i chuá»—i
        return str;
    },
    removeVietnameseTones:function (str) {
        str = str.replace(/à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ/g,"a");
        str = str.replace(/è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ/g,"e");
        str = str.replace(/ì|í|ị|ỉ|ĩ/g,"i");
        str = str.replace(/ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ/g,"o");
        str = str.replace(/ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ/g,"u");
        str = str.replace(/ỳ|ý|ỵ|ỷ|ỹ/g,"y");
        str = str.replace(/đ/g,"d");
        str = str.replace(/À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ/g, "A");
        str = str.replace(/È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ/g, "E");
        str = str.replace(/Ì|Í|Ị|Ỉ|Ĩ/g, "I");
        str = str.replace(/Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ/g, "O");
        str = str.replace(/Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ/g, "U");
        str = str.replace(/Ỳ|Ý|Ỵ|Ỷ|Ỹ/g, "Y");
        str = str.replace(/Đ/g, "D");
        // Some system encode vietnamese combining accent as individual utf-8 characters
        // Một vài bộ encode coi các dấu mũ, dấu chữ như một kí tự riêng biệt nên thêm hai dòng này
        str = str.replace(/\u0300|\u0301|\u0303|\u0309|\u0323/g, ""); // ̀ ́ ̃ ̉ ̣  huyền, sắc, ngã, hỏi, nặng
        str = str.replace(/\u02C6|\u0306|\u031B/g, ""); // ˆ ̆ ̛  Â, Ê, Ă, Ơ, Ư
        // Remove extra spaces
        // Bỏ các khoảng trắng liền nhau
        str = str.replace(/ + /g," ");
        str = str.trim();
        // Remove punctuations
        // Bỏ dấu câu, kí tự đặc biệt
        str = str.replace(/!|@|%|\^|\*|\(|\)|\+|\=|\<|\>|\?|\/|,|\.|\:|\;|\'|\"|\&|\#|\[|\]|~|\$|_|`|-|{|}|\||\\/g," ");
        return str;
    },
    /*******************************************************************/
    /***************************** VALIDATION **************************/
    validateEmail: function (email) {
        var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(email);
    },
    /*******************************************************************/
    /***************************** END VALIDATION **************************/

    showLoading: function () {
        if($('#overlay').length==0){
            $('body').append('<div id="overlay"></div>');
            $('body').append('<div id="countpercent"></div>');
            $('body').append('<div id="loader"></div>');
        }

    },

    endLoading: function () {
        $('#overlay').remove();
        $('#countpercent').remove();
        $('#loader').remove();
    },

    strReplace: function (search, replace, str) {
        return str.replaceAll(search, replace);
    },

    stringtoNumber: function (str) {
        str = ("" + str).replace(/,/g, "");
        var num = str * 1;
        return Number(num);
    },

    formateNumber: function (num) {
        if (num == "")
            return 0;

        num = String(num).replace(/,/g, "");
        num = Number(num);
        ar = ("" + num).split("\.");
        div = ar[0];
        mod = ar[1];
        //console.log(div + '--' + mod);
        arr = new Array();
        block = "";

        for (i = div.length - 1; i >= 0; i--) {

            block = div[i] + block;

            if (block.length == 3) {
                arr.unshift(block);
                block = "";
            }

        }
        arr.unshift(block);

        divnum = arr.join(",");
        divnum = this.trim(divnum, ",")
        divnum = divnum.replace("-,", "-")

        if (mod == undefined) {

            return divnum;
        } else {
            var p = mod.substr(0, 4);
            return divnum + "\." + p;
        }

    },

    formatDouble: function (num, c, d, t) {
        var n = num,
            c = isNaN(c = Math.abs(c)) ? 0 : c,
            d = d == undefined ? "." : d,
            t = t == undefined ? "," : t,
            s = n < 0 ? "-" : "",
            i = String(parseInt(n = Math.abs(Number(n) || 0).toFixed(c))),
            j = (j = i.length) > 3 ? j % 3 : 0;
        return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
    },

    currencyFormate: function (num) {
        var n = this.formatDouble(num);
        return currencyprefix + n + currencysubfix;
    },

    isNumber: function (char) {
        if (char != 8 && char != 0 && (char < 45 || char > 57)) {
            //display error message
            //$("#errmsg").html("Digits Only").show().fadeOut("slow");
            return false;
        } else true
    },

    trim: function (str, chars) {
        var str = this.ltrim(this.rtrim(str, chars), chars);
        str = this.rtrim(this.rtrim(str, chars), chars);
        return str;
    },

    ltrim: function (str, chars) {
        chars = chars || "\\s";
        return str.replace(new RegExp("^[" + chars + "]+", "g"), "");
    },

    rtrim: function (str, chars) {
        chars = chars || "\\s";
        return str.replace(new RegExp("[" + chars + "]+$", "g"), "");
    },

    loopString: function (str, numloop) {
        var s = '';
        for (var i = 0; i < numloop; i++) {
            s += str;
        }
        return s;
    },

    numberReady: function () {
        $(".number").change(function (e) {
            num = common.formateNumber($(this).val().replace(/,/g, ""));
            $(this).val(num)
        });
        $(".number").keypress(function (e) {
            return common.isNumber(e.which);
        });
        $('.number').focus(function (e) {
            if (this.value == 0)
                this.value = "";
        });
        $('.number').blur(function (e) {
            if (this.value == "")
                this.value = 0;
        });
        $(".number").each(function (index) {
            $(this).val(common.formateNumber($(this).val()));
        });
    },

    toDateNumber: function (num) {
        if (num < 10)
            return "0" + num;
        else
            return num;
    },
    converToLocalTime:function (datesqlsqlstring){
        var arr = datesqlsqlstring.split(' ');
        if(arr.length == 1){
            return dateString+"T00:00";
        }else {
            var time = arr[1].split(':');
            return arr[0]+"T"+time[0]+":"+time[1];
        }
    },
    convertDMYtoDate:function (dateString,chr) {
        if(chr==undefined){
            chr = '-';
        }
        var arr = dateString.split(' ');
        if(arr.length == 1){
            var dateParts = dateString.split(chr);
// month is 0-based, that's why we need dataParts[1] - 1
            return new Date(+dateParts[2], dateParts[1] - 1, +dateParts[0]);
        }else {
            var dateParts = arr[0].split(chr);
            var time = arr[1].split(':');
// month is 0-based, that's why we need dataParts[1] - 1
            return new Date(+dateParts[2], dateParts[1] - 1, +dateParts[0],time[0],time[1],time[2]);
        }

    },
    dateShow: function (dt,formate) {
        dt = new Date(dt);
        if(dt == "Invalid Date"){
            return ''
        }else {
            if(formate == undefined || formate =='DMY'){
                return this.toDateNumber(dt.getDate()) + "-" + this.toDateNumber(dt.getMonth() + 1) + "-" + dt.getFullYear();
            }
            if(formate =='YMD'){
                return  dt.getFullYear() + "-" + this.toDateNumber(dt.getMonth() + 1) + "-" + this.toDateNumber(dt.getDate());
            }
            if(formate =='MDY'){
                return this.toDateNumber(dt.getMonth() + 1) + "-" + this.toDateNumber(dt.getDate()) + "-" + dt.getFullYear();
            }
        }
    },
    dateTimeShow: function (dt,formate) {
        dt = new Date(dt);
        if(dt == "Invalid Date"){
            return ''
        }else {
            if(formate == undefined || formate =='DMY'){
                return this.toDateNumber(dt.getDate()) + "-" + this.toDateNumber(dt.getMonth() + 1) + "-" + dt.getFullYear()
                    + ' ' + this.toDateNumber(dt.getHours()) + ':' + this.toDateNumber(dt.getMinutes()) + ':' + this.toDateNumber(dt.getSeconds());
            }
            if(formate =='YMD'){
                return  dt.getFullYear() + "-" + this.toDateNumber(dt.getMonth() + 1) + "-" + this.toDateNumber(dt.getDate())
                    + ' ' + this.toDateNumber(dt.getHours()) + ':' + this.toDateNumber(dt.getMinutes()) + ':' + this.toDateNumber(dt.getSeconds());
            }
            if(formate =='MDY'){
                return this.toDateNumber(dt.getMonth() + 1) + "-" + this.toDateNumber(dt.getDate()) + "-" + dt.getFullYear()
                    + ' ' + this.toDateNumber(dt.getHours()) + ':' + this.toDateNumber(dt.getMinutes()) + ':' + this.toDateNumber(dt.getSeconds());
            }
        }
    },
    timeShow: function (dt) {
        dt = new Date(dt);
        var time = "";
        if (dt.getHours() < 12) {
            time = dt.getHours() + ":" + this.toDateNumber(dt.getMinutes()) + " am";
        } else {
            if (dt.getHours() == 12)
                time = dt.getHours() + ":" + this.toDateNumber(dt.getMinutes()) + " pm";
            else
                time = time = dt.getHours() - 12 + ":" + this.toDateNumber(dt.getMinutes()) + " pm";
        }

        return time;
    },

    dateView: function (datemysql) {
        if(datemysql == '0000-00-00'){
            return '';
        }
        var t = datemysql.split(/[- :]/);
        var dt = new Date(Number(t[0]), Number(t[1]) - 1, Number(t[2]), Number(t[3]!=undefined?t[3]:0), Number(t[4]!=undefined?t[4]:0), Number(t[5]!=undefined?t[5]:0));
        return this.dateShow(dt)
    },

    timeView: function (time) {
        var t = time.split(':');
        return t[0] + ':' + t[1] + ':'+t[2];
    },

    showFullDate: function (dt) {
        var d = new Date(dt);
        var day = new Array();
        day[0] = "Sunday";
        day[1] = "Monday";
        day[2] = "Tueday";
        day[3] = "Wednesday";
        day[4] = "Thursday";
        day[5] = "Friday";
        day[6] = "Saturday";
        var month = new Array();
        month[0] = "January";
        month[1] = "February";
        month[2] = "March";
        month[3] = "April";
        month[4] = "May";
        month[5] = "June";
        month[6] = "July";
        month[7] = "August";
        month[8] = "September";
        month[9] = "October";
        month[10] = "November";
        month[11] = "December";

        return day[d.getDay()] + " " + d.getDate() + " " + month[d.getMonth()] + " " + this.timeShow(d);
    },

    PrintElem: function (elem) {
        var mywindow = window.open('', 'PRINT', 'height=400,width=600');

        mywindow.document.write('<html><head><title>' + document.title + '</title>');
        mywindow.document.write('</head><body >');
        mywindow.document.write(document.getElementById(elem).innerHTML);
        mywindow.document.write('</body></html>');

        mywindow.document.close(); // necessary for IE >= 10
        mywindow.focus(); // necessary for IE >= 10*/

        mywindow.print();
        mywindow.close();

        return true;
    },

    PrintHtml: function (html) {
        var mywindow = window.open('', 'PRINT', 'height=500,width=800');

        mywindow.document.write('<html><head><title>' + document.title + '</title>');
        mywindow.document.write('</head><body >');
        mywindow.document.write(html);
        mywindow.document.write('</body></html>');

        mywindow.document.close(); // necessary for IE >= 10
        mywindow.focus(); // necessary for IE >= 10*/
        setTimeout(function () {
            mywindow.print();
            mywindow.close();
        }, 2000)


        return true;
    },
//File
    getFileName: function (fullPath) {
        if (fullPath) {
            var startIndex = (fullPath.indexOf('\\') >= 0 ? fullPath.lastIndexOf('\\') : fullPath.lastIndexOf('/'));
            var filename = fullPath.substring(startIndex);
            if (filename.indexOf('\\') === 0 || filename.indexOf('/') === 0) {
                filename = filename.substring(1);
            }
            return filename;
        }
    },

    download: function (data, filename, type) {
        var file = new Blob([data], {type: type});
        if (window.navigator.msSaveOrOpenBlob) // IE10+
            window.navigator.msSaveOrOpenBlob(file, filename);
        else { // Others
            var a = document.createElement("a"),
                url = URL.createObjectURL(file);
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            setTimeout(function () {
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);
            }, 0);
        }
    },
    openModal: function (title, content, textOK, textCancel, element, callback) {
        var html = '<div class="modal" id="modalPopup">' +
            '    <div class="modal-dialog modal-lg">' +
            '        <div class="modal-content">' +
            '            <!-- Modal Header -->' +
            '            <div class="modal-header">' +
            '                <h4 class="modal-title">' + title + '</h4>' +
            '                <button type="button" class="close" data-dismiss="modal">&times;</button>' +
            '            </div>' +
            '            <!-- Modal body -->' +
            '            <div class="modal-body">' + content + '</div>' +
            '            <!-- Modal footer -->' +
            '            <div class="modal-footer">' +
            '                <button type="button" class="btn btn-success" id="btnOk"><i class="fa fa-check"></i> ' + textOK + '</button>' +
            '                <button type="button" class="btn btn-inverse" data-dismiss="modal"><i class="fa fa-close"></i> ' + textCancel + '</button>' +
            '            </div>' +
            '        </div>' +
            '    </div>' +
            '</div>';
        $('body').append(html);
        $("#modalPopup").on('shown.bs.modal', function(){
            console.log('showModalPopup');
            $(document).triggerAll('showModalPopup');
        });
        $('#modalPopup').modal();
        $('#btnOk').unbind('click');
        $('#btnOk').click(function () {
            callback(element);
        });
        $("#modalPopup").on('hide.bs.modal', function(){
            $('#modalPopup').remove();
        });
    },
    closeModal: function () {
        $('#modalPopup').modal('hide');
        $('#modalPopup').remove();
    },
    resetProgressBar: function () {
        $('.bar').html('');
        $('.progress .bar').css(
            'width', 0
        );
    },
    decodeBase64Unicode:function (str) {
        return decodeURIComponent(atob(str).split('').map(function (c) {
            return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
        }).join(''));
    },
    async uploadMultiFile(tablename,id,formData) {
        const response = await fetch("?route=Common/File/Upload&folder=" + tablename + "/" + id, {
            method: "POST",
            body: formData
        });
        const result = response.json();
        return result;
    },
    stringToArray:function (str) {
        if(str == undefined || str == null){
            return [];
        }
        str = str.replaceAll('[','');
        str = str.replaceAll(']','');
        var arr = str.split(',');
        return arr;
    },
    arrayToString:function (arr) {
        return '['+arr.join('],[')+']';
    }
}
String.prototype.replaceAll = function (search, replacement) {
    var target = this;
    return target.split(search).join(replacement);
};
$(document).ready(function () {
    common.numberReady();
});