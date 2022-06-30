$(document).ready(function(){

    var last_boat_calendar_week_retrieved = null;

    function load_a_week(calendar, cb){
        var data = {
			
            "action" : "get_boat_calendar",
        };

        if (last_boat_calendar_week_retrieved){
            data.last_retrieved = last_boat_calendar_week_retrieved;
        }

        $.ajax({
            type: 'GET',
            url: "ajax.php",
            data: data,
            dataType: "json",
            success: function(data) {
                if (cb){
                    cb();
                }
                if (!data.success || !data.markup){
                    if (data.error_msg){
                        console.log("Error trying to load a week: " + data.error_msg);
                    }
                    else {
                        console.log("Error trying to load a week: data returned not in expected format");
                    }
                    calendar.hide();
                    return;
                }

                var markup_el = $(data.markup);
                calendar.find(".month_wrap").append(markup_el);
                last_boat_calendar_week_retrieved = data.last_retrieved;

            },
            error: function(jqXHR, textStatus){
                if (cb){
                    cb();
                }
                console.log("Error trying to load a week: network error, can't load calendar");
                calendar.hide();
            }
        });
        
    }
    
    var boat_calendar = $("#boat_calendar");

    if (boat_calendar.length){
    
        boat_calendar.data("scroll_amount", 0);

        load_a_week(boat_calendar);

        $(".scroll_up").click(function(e){
            e.preventDefault();

            var scroll_amount = boat_calendar.data("scroll_amount");
            if (!scroll_amount){
                return;
            }
            scroll_amount--;
            boat_calendar.find(".month_wrap").animate({
                "top" : "-" + (scroll_amount * 20) + "%"
            },
            {
                duration: 100,
            }
            );
            boat_calendar.data("scroll_amount", scroll_amount);
        });
    
        var scroll_disabled = false;
        $(".scroll_down").click(function(e){
            e.preventDefault();
            if (scroll_disabled){
                return;
            }
            scroll_disabled = true;

            load_a_week(boat_calendar, function(){
                scroll_disabled = false;
            });

            var scroll_amount = boat_calendar.data("scroll_amount");
            scroll_amount++;
            boat_calendar.find(".month_wrap").animate({
                "top" : "-" + (scroll_amount * 20) + "%"
            },
            {
                duration: 100,
            }
            );
            boat_calendar.data("scroll_amount", scroll_amount);
        });
        
    }

});
