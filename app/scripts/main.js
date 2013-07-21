$(document).ready(function() {

	var $nav = $('.Navigation--primary');
	$('.page-Index').find($nav).removeClass('is-hidden');

	var $main = $('.l-Main');
	if ($main.find('img').length > 0) {
		$main.imagesLoaded( function() {
			$('html').addClass('loaded');
			//console.log('images loaded');
		});
	} else {
		$('html').addClass('loaded');
	}

	var $zcanvas = $('#zlayer');
	if ($zcanvas.length > 0) {
		$zcanvas.zlayer([
			{
				layer: '.Layer1',
				mass: 20,
				confine: 'y'
			},
			{
				layer: '.Layer2',
				mass: 7,
				force: 'push'
			},
			{
				layer: '.Layer3',
				mass: 11,
				force: 'pull'
			},
			{
				layer: '.Layer4',
				mass: 120,
				force: 'push'
			},
			{
				layer: '.Layer5',
				mass: 60
			}
		], {
			repositionTransition:'all 500s ease-in'
		});
	}


	// Clickable dropdowns (touch4evah)
	// -------------------------------
	$('.DropdownParent').children('a').click(function(event) {
		event.preventDefault();
		$(this).parent().toggleClass('js-hover');
	});

});
