$(function(){
  $('.menu-toggle').on('click', function(){ $('.nav').toggleClass('open'); });
  $(document).on('submit','.delete-form',function(e){
    if(!window.confirm('Confirmer cette suppression ?')) e.preventDefault();
  });
  $('.alert').each(function(){const el=$(this);setTimeout(function(){el.fadeOut(350)},6000);});
});