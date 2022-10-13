$(document).on('loadViewReady', function () {
    Product.showField();
});
$(document).on('loadFormReady', function () {
    Product.showField();
});
Product = {
    showField:function () {
        if(app.recordData.productparent == 0){
            $('#col_clothessize').remove();
            $('#col_accessorysize').remove();
        }
    }
}