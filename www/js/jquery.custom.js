$(document).ready(function() {
    $('.datetime-local').each(function() {
    datetime = $(this).val();
    $(this).val(datetime.substring(0, 10) + ' ' + datetime.substring(11, 16));
    });
    $('.datetime-local').datetimepicker({
      format: 'yyyy-mm-dd hh:ii',
      weekStart: 1,
      bootcssVer: 3
  });
}); 