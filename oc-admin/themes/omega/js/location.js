function check_form_country() {
  var error = 0;
  if ( $("#c_country").val().length != 2 ) {
    $("#c_country").css('border','1px solid red');
    error = error + 1;
  }
  if ( $("#country").val().length < 2 ) {
    $("#country").css('border','1px solid red');
    error = error + 1;
  }
  if(error > 0) {
    $('#c_code_error').css('display','block');
    return false;
  }
  return true;
}

function edit_countries(element) {
  var d_country = $('#d_edit_country');

  d_country.css('display','block');
  $('#fade').css('display','block');

  $("input[name=country_code]").val(element.attr('code'));
  $("input[name='e_country']").val(element.attr('data'));
  $("input[name='e_country_native']").val(element.attr('countryNative'));
  $("input[name='e_country_phone_code']").val(element.attr('phoneCode'));
  $("input[name='e_country_currency']").val(element.attr('currency'));
  //console.log(element);
  $("input[name='e_country_slug']").val(element.attr('slug'));
  renderEditCountry();
  return false;
}

function edit_region(element, id) {
  var d_region = $('#d_edit_region');

  d_region.css('display','block');
  $('#fade').css('display','block');

  $("input[name=region_id]").val(id);
  $("input[name=e_region]").val(element.html());
  $("input[name=e_region_native]").val(element.attr('regionNative'));
  $("input[name=e_region_slug]").val(element.attr('slug'));

  renderEditRegion();
  return false;
}

function edit_city(element, id) {
  var d_city = $('#d_edit_city');

  d_city.css('display','block');
  $('#fade').css('display','block');

  $("input[name=city_id]").val(id);
  $("input[name=e_city]").val(element.html());
  $("input[name=e_city_slug]").val(element.attr('slug'));
  $("input[name=e_city_native]").val(element.attr('cityNative'));
  $("input[name=e_city_lat]").val(element.attr('cityLat'));
  $("input[name=e_city_long]").val(element.attr('cityLong'));
  renderEditCity();
  return false;
}

function show_region(c_code, s_country) {
  $.ajax({
    "url": base_url + "index.php?page=ajax&action=regions&countryId=" + c_code,
    "dataType": 'json',
    success: function( json ) {
      var div_regions = $("#i_regions").html('');
      var isempty = true;
      $('#i_cities').html('');
      
      $.each(json, function(i, val){
        isempty = false;

        var clear = $('<div>').css('clear','both');
        var container = $('<div>');
        var s_country = $('<div>').css('float','left');
        var more_region = $('<div>').css('float','right');
        var link = $('<a>').addClass('view-more btn btn-green');
        
        s_country.append('<div class="trr"><span class="checkboxr" style="*visibility:hidden;"><input type="checkbox" name="region[]" value="'+val.pk_i_id+'" ></span><a href="javascript:void(0);" class="edit" onclick="edit_region($(this), ' + val.pk_i_id + ');" style="*padding-right: 15px;" slug="'+val.s_slug+'" regionNative="'+oscEscapeHTML(val.s_name_native)+'">' + val.s_name + '</a></div>');
        link.attr('href', 'javascript:void(0)');
        link.click(function(){
          show_city(val.pk_i_id);
        });
        
        link.append(s_view_more);
        more_region.append('<a class="close btn btn-white" onclick="return delete_dialog(\'' + val.pk_i_id + '\', \'delete_region\');" href="' + base_url + 'index.php?page=settings&action=locations&type=delete_region&id[]=' + val.pk_i_id + '"><i class="fa fa-trash"></i></a>');
        more_region.append(link);
        container.append(s_country).append(more_region);
        div_regions.append(container);
        // div_regions.append(clear);
      });

      console.log(isempty);
      
      if(isempty === true) {
        div_regions.append('<div style="color:#666;font-style:italic">' + noentryCountry + ' ' + c_code + '</div>');
      }
      
      $(".trr").off("mouseenter");
      $(".trr").off("mouseleave");

      $(".trr").on("mouseenter", function() {
        // $(this).find(".checkboxr").css({ 'visibility': ''});
      });

      $(".trr").on("mouseleave", function() {
        if (!$(this).find(".checkboxr input").is(':checked')) {
          // $(this).find(".checkboxr").css({ 'visibility': 'hidden'});
        }
        if($(".checkboxr input:checked").length>0) {
          $("#b_remove_region").show();
        } else {
          $("#b_remove_region").hide();
        }
      });
      resetLayout();
      hook_load_cities();
    }
  });
  
  $('input[name=country_c_parent]').val(c_code);
  $('input[name=country_parent]').val(s_country);
  $('#b_new_region').css('display','block');
  $('#b_new_city').css('display','none');
  return false;
}

function show_city(id_region) {
  $.ajax({
    "url": base_url + "index.php?page=ajax&action=cities&regionId=" + id_region,
    "dataType": 'json',
    success: function( json ) {
      //console.log(json);
      var div_regions = $("#i_cities").html('');
      var isempty = true;
      
      $.each(json, function(i, val){
        isempty = false;
        var clear = $('<div>').css('clear','both');
        var container = $('<div>');
        var s_region = $('<div>').css('float','left');
        var more_city = $('<div>').css('float','right');

        var cityLat = val.d_coord_lat;
        if(val.d_coord_lat === null || val.d_coord_lat === undefined || val.d_coord_lat == 0 || val.d_coord_lat == '') {
          cityLat = '';
        }

        var cityLong = val.d_coord_long;
        if(val.d_coord_long === null || val.d_coord_long === undefined || val.d_coord_long == 0 || val.d_coord_long == '') {
          cityLong = '';
        }

        s_region.append('<div class="trct"><span class="checkboxct" style="*visibility:hidden;"><input type="checkbox" name="city[]" value="'+val.pk_i_id+'" ></span><a href="javascript:void(0);" class="edit" onclick="edit_city($(this), ' + val.pk_i_id + ');" style="*padding-right: 15px;" slug="'+val.s_slug+'" cityNative="'+oscEscapeHTML(val.s_name_native)+'" cityLat="'+cityLat+'" cityLong="'+cityLong+'">' + val.s_name + '</a></div>');
        more_city.append('<a class="delete btn btn-white" class="close" onclick="return delete_dialog(\'' + val.pk_i_id + '\', \'delete_city\');"  href="' + base_url + 'index.php?page=settings&action=locations&type=delete_city&id=' + val.pk_i_id + '"><i class="fa fa-trash"></i></a>');
        container.append(s_region).append(more_city);
        div_regions.append(container);
        // div_regions.append(clear);
      });
      
      if(isempty === true) {
        div_regions.append('<div style="color:#666;font-style:italic">' + noentryRegion + ' ' + id_region + '</div>');
      }
      
      $(".trct").off("mouseenter");
      $(".trct").off("mouseleave");

      $(".trct").on("mouseenter", function() {
        // $(this).find(".checkboxct").css({ 'visibility': ''});
      });

      $(".trct").on("mouseleave", function() {
        if (!$(this).find(".checkboxct input").is(':checked')) {
          // $(this).find(".checkboxct").css({ 'visibility': 'hidden'});
        }
        if($(".checkboxct input:checked").length>0) {
          $("#b_remove_city").show();
        } else {
          $("#b_remove_city").hide();
        }
      });
      
      resetLayout();
    }, 
    error: function(data) {
      console.log(data);
    }
  });
  $('#b_new_city').css('display','block');
  $('input[name=region_parent]').val(id_region);
  return false;
}

$(document).ready(function(){
  $("#c_country").focus(function(){
    $('#c_code_error').css('display','none');
     $(this).css('border','');
  });

  $("#country").focus(function(){
     $(this).css('border','');
  });

  $("#country").keyup(function(){
    if($('#country').val().length == 0) {
       $('input[name=c_manual]').val('1');
    }
  });

  $("#b_new_country").click(function(){
    renderNewCountry();
  });
  $("#b_import").click(function(){
    renderImport();
  });
  $("#b_new_region").click(function(){
    renderAddRegion();
  });
  $("#b_new_city").click(function(){
    renderAddCity();
  });
});

function renderNewCountry(){
  var dialogWidth = 400;
  
  if($(window).width() < 460) {
    dialogWidth = $(window).width() - 40;
  }
  
  $( "#d_add_country" ).dialog({
    width: dialogWidth,
    modal: true,
    title: addNewCountryText,
  });
}

function renderImport(){
  var dialogWidth = 400;
  
  if($(window).width() < 460) {
    dialogWidth = $(window).width() - 40;
  }
    
  $( "#dialog-location-import" ).dialog({
    width: dialogWidth,
    modal: true,
    title: importLocationText,
  });
}

function renderEditCountry(){
  var buttonsActions = {};
  buttonsActions[editText] = function() {
    $("#d_edit_country_form").submit();
  }
  
  buttonsActions[cancelText] = function() {
    $(this).dialog("close");
  }
  
  var dialogWidth = 400;
  
  if($(window).width() < 460) {
    dialogWidth = $(window).width() - 40;
  }
  
  $( "#d_edit_country" ).dialog({
    width: dialogWidth,
    modal: true,
    title: editNewCountryText
  });
}
function renderAddRegion(){
  var dialogWidth = 400;
  
  if($(window).width() < 460) {
    dialogWidth = $(window).width() - 40;
  }
  
  $( "#d_add_region" ).dialog({
    width: dialogWidth,
    modal: true,
    title: addNewRegionText
  });
}
function renderEditRegion(){
  var dialogWidth = 400;
  
  if($(window).width() < 460) {
    dialogWidth = $(window).width() - 40;
  }
  
  $( "#d_edit_region" ).dialog({
    width: dialogWidth,
    modal: true,
    title: editNewRegionText
  });
}
function renderAddCity(){
  var dialogWidth = 400;
  
  if($(window).width() < 460) {
    dialogWidth = $(window).width() - 40;
  }
  
  $( "#d_add_city" ).dialog({
    width: dialogWidth,
    modal: true,
    title: addNewCityText
  });
}
function renderEditCity(){
  var dialogWidth = 400;
  
  if($(window).width() < 460) {
    dialogWidth = $(window).width() - 40;
  }
  
  $( "#d_edit_city" ).dialog({
    width: dialogWidth,
    modal: true,
    title: editNewCityText
  });
}