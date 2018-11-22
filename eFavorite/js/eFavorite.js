var eFavorite = {
    params: {
        addText: 'добавить в избранное',
        removeText: 'удалить из избранного',
        elementTotalId: 'favorits_cnt',
        elementClass: 'favorite',
        elementActiveClass: 'active',
        lifetime: 2592000
    },
    action: function(id, obj) {
        var self = this;
        var id = (!!id) ? id : '';
        var obj = (!!obj) ? obj : false;
        var data2 = 'action=eFavorite&lifetime=' + this.params.lifetime;
        if (id != '') {
            data2 += '&id=' + id;
        }
        $.ajax({
            url: "assets/snippets/eFavorite/ajax.php",                                   
            data: data2,
            type: "POST",
            cache: false,
            dataType: 'json',
            beforeSend:function() {},                   
            success: function(msg) {
                var total = msg.total ? msg.total : 0;
                if (total == 0) total = '';
                $("#" + self.params.elementTotalId).html(total);
                var rows = msg.rows;
                $("." + self.params.elementClass).removeClass(self.params.elementActiveClass).attr("title", self.params.addText);
                for (var key in rows) {
                    $("." + self.params.elementClass + "[data-id='" + rows[key].id + "']").addClass(self.params.elementActiveClass).attr("title", self.params.removeText);
                }
            }
        })
    },
    bind: function() {
        $(document).on("click", ".favorite", function(){
            var id = $(this).data("id");
            eFavorite.action(id, $(this));
        })
    },
    init: function() {
        this.params = $.extend(this.params, eFavoriteParams);
        this.action();
        this.bind();
    }
};

$(document).ready(function(){
    eFavorite.init();
})