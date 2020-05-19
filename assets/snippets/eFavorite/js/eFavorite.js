var eFavorite = function() {
    this.start();
}
eFavorite.prototype = {
    defaults: {
        addText: 'добавить в избранное',
        removeText: 'удалить из избранного',
        elementTotalId: 'favorits_cnt',
        elementClass: 'favorite',
        elementActiveClass: 'active',
        lifetime: 2592000,
        className: 'eFavorite',
        id: 'favorite'
    },
    action: function(id) {
        var self = this;
        var id = (!!id) ? id : '';
        var data2 = 'action=eFavorite&lifetime=' + self.params.lifetime + '&className=' + self.params.className + '&id=' + self.params.id;;
        if (id != '') {
            data2 += '&docid=' + id;
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
                if (typeof eFavoriteRefresh === 'function') {
                    eFavoriteRefresh();
                }
            }
        })
    },
    bind: function() {
        var self = this;
        $(document).on("click", "." + self.params.elementClass, function(e){
            e.preventDefault();
            var id = $(this).data("id");
            self.action(id);
        })
    },
    init: function() {
        this.action();
        this.bind();
    },
    start: function() {
        this.params = $.extend({}, this.defaults);
    }
};
