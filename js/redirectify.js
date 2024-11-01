(function($) {
  function updateRedirect() {
    if ( ( $.trim( $( '#redirectify' ).val() ) === '' ) && ( $( '#redirectifydiv' ).siblings( 'strong' ).text() === 'Enabled' ) ) {
      $( '#redirectifydiv' ).siblings( 'strong' ).text( 'Disabled' );
    } else {
      $( '#redirectifydiv' ).siblings( 'strong' ).text( 'Enabled' );
    }
  }

  $( '#redirectifydiv' ).siblings( 'a.edit-redirectify' ).click( function( event ) {
    if ( $( '#redirectifydiv' ).is( ':hidden' ) ) {
      $( '#redirectifydiv' ).slideDown( 'fast' ).find( 'input' ).focus();
      $( this ).hide();
    }

    event.preventDefault();
  });

  $( '#redirectifydiv' ).find( '.cancel-redirectify' ).click( function( event ) {
    $( '#redirectifydiv' ).slideUp( 'fast' ).siblings( 'a.edit-redirectify' ).show().focus();
    $( '#redirectify' ).val( $( '#hidden-redirectify ').val() );
    updateRedirect();

    event.preventDefault();
  });

  $( '#redirectifydiv' ).find( '.save-redirectify' ).click( function( event ) {
    $( '#redirectifydiv' ).slideUp( 'fast' ).siblings( 'a.edit-redirectify' ).show();
    updateRedirect();

    event.preventDefault();
  });
}(jQuery));