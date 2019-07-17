jQuery(document).ready(function () {
    let edit_form = $('#cat_edit_form');
    let remove_form = $('#cat_remove_form');

    $('.cat-edit').click(function () {
        let that = $(this);

        $('#cat_modal').modal();

        // Get category data from <tr> and Fill data to form
        let tr = $('#cat_manager_table tr[data-id=' + that.data('id') + ']');
        for (let datum in tr.data()) {
            let input = edit_form.find('[name="cat_' + datum + '"]');
            if (datum === 'enabled') {
                input.prop('checked',tr.data(datum) ? 'checked' : '');
            } else {
                input.val(tr.data(datum));
            }
        }
    });

    $('.cat-remove').click(function () {
        let that = $(this);
        if (confirm('Confirm to remove this Category ?')) {
            remove_form.find('input[name=cat_id]').val(that.data('id'));
            remove_form.submit();
        }
    });

});
