jQuery(document).ready(function($) {
    var carousels = [];

    $(".owl-carousel").owlCarousel({
        items: 3,
        loop: true,
        autoplay: true,
        autoplayTimeout: 5000,
        autoplayHoverPause: true,
        autoHeight: true,
        responsiveClass: true,
        navText: ['< Rückwärts','Vorwärts >'],
        responsive: {
            0: {
                items: 1,
                nav: true,
                dots: false
            },
            1000: {
                items: 2,
                nav: false
            },
            1500: {
                items: 3,
                nav: false
            }
        }
    });

    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    $(".sofvRanking").each(function(index, value) {
        var ajaxurl = $(value).attr('ajaxurl');
        var url = $(value).attr('url');
        $.ajax({
            url: ajaxurl,
            data: {
                'action': 'sofvRankingRequest',
                'url': url
            }, success: function (data) {
                $(value).html(data).tooltip();
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                });
            },
            error: function (error) {
            }
        });
    });

    $(".sofvGames").each(function(index, value) {
        var ajaxurl = $(value).attr('ajaxurl');
        var url = $(value).attr('url');
        var type = $(value).attr('type');
        var groupby = $(value).attr('groupby');
        var resultmode = $(value).attr('resultmode');
        console.log('sofvGames');
        $.ajax({
            url: ajaxurl,
            data: {
                'action': 'sofvGamesRequest',
                'url': url,
                'type': type,
                'groupby': groupby,
                'resultmode': resultmode
            }, success: function (data) {
                $(value).html(data).tooltip();
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                });
                if (resultmode === 'renderCarousel') {
                    $('.carousel').each(function() {
                        var $this = $(this);
                        var id = $this.attr('id');
                        carousels[id] = $(this).carousel();
                        $this.find('.ccn').on('click', function(evt) {
                            carousels[id].carousel('next');
                            evt.preventDefault();
                            evt.stopPropagation();
                        });
                        $this.find('.ccp').on('click', function(evt) {
                            carousels[id].carousel('prev');
                            evt.preventDefault();
                            evt.stopPropagation();
                        });
                    });
                }
            },
            error: function (error) {
                console.log('error');
                console.log(error);
            }
        });
    });


});