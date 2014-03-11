function initSlide(e, t, n) {
    var r = KISSY,
    i = r.DOM,
    s = r.Event;
    r.UA.ie && r.each(i.query(".f-con-rightsec"),
    function(e, t) {
        var n = i.query("a", e);
        s.on(n, "mouseover",
        function(e) {
            i.css(i.get(".floorgoods-limit-buy", this), {
                bottom: "66px"
            }),
            i.css(i.get(".floorgoods-brandname", this), {
                bottom: "44px"
            })
        }),
        s.on(n, "mouseout",
        function(e) {
            i.css(i.get(".floorgoods-limit-buy", this), {
                bottom: "-22px"
            }),
            i.css(i.get(".floorgoods-brandname", this), {
                bottom: "-22px"
            })
        })
    });
    if (!r.get(e)) return;
    r.use("switchable",
    function(r, i) {
        new r.Slide(e, {
            navCls: t,
            contentCls: n,
            viewSize: 190,
            autoplay: !1,
            effect: "scrollx",
            easing: "easeOutStrong",
            circular: !0
        })
    })
}
KISSY.add("category/category",
function(e) {
    function m(n, r) {
        var i = this;
        if (! (i instanceof m)) return new m(n, r);
        i.container = e.get(n),
        i.config = e.merge(l, r || {}),
        i.config.viewer = e.get(r.viewId, i.container),
        i.triggers = t.query(r.triggers, i.container),
        i._init()
    }
    function g(e, t) {
        for (var n = 0; n < e.length; n += 1) if (e[n] === t) return n;
        return - 1
    }
    var e = KISSY,
    t = e.DOM,
    n = e.Event,
    r = window,
    i = document,
    s = e.UA.ie == 6,
    o = "selected",
    u = "hidden",
    a = "mouseenter",
    f = "mouseleave",
    l = {
        showDelay: .1,
        hideDelay: .1,
        viewId: null,
        subViews: null,
        triggers: null,
        lazyload: !1,
        dataUrl: null
    },
    c = "755px",
    h = 10,
    p = 10,
    d = !0,
    v = !1;
    return e.mix(m.prototype, {
        changeTrigger: function(n) {
            var r = this,
            i = r.triggers;
            e.each(i,
            function(e) {
                t.removeClass(e, o)
            }),
            t.addClass(i[n], o)
        },
        changeView: function(n) {
            var r = this;
            e.each(r.subViews,
            function(e) {
                t.addClass(e, u)
            }),
            t.removeClass(n, u);
            var i = t.height(n);
            t.height(r.viewer, i + h + p + "px"),
            r.resetPostion(),
            r.shadow || (r.shadow = t.get(".shadow", r.viewer)),
            t.height(r.shadow, i + p + "px")
        },
        show: function() {
            var n = this,
            r = n.config,
            i = n.subViews,
            o = r.idx,
            u = n.isDataReady ? i[o] : i[0],
            a = t.width(n.viewer);
            n.hideTimer && clearTimeout(n.hideTimer),
            d && a == 0 ? (v = !1, d = !1, n.expandTimer && clearTimeout(n.expandTimer), n.expandTimer = setTimeout(function() {
                n.changeTrigger(o),
                n.changeView(u),
                s ? t.width(n.viewer, c) : new e.Anim(n.viewer, {
                    width: c
                },
                .2, "linear").run()
            },
            r.showDelay * 1e3)) : (n.resetTimer && clearTimeout(n.resetTimer), n.resetTimer = setTimeout(function() {
                n.status == "visible" && (n.changeTrigger(o), n.changeView(u))
            },
            r.showDelay * 1e3))
        },
        hide: function(n) {
            var r = this,
            i = r.config,
            s = r.triggers;
            r.status = "hidden",
            d = !0,
            r.viewer && (r.expandTimer && clearTimeout(r.expandTimer), r.hideTimer && clearTimeout(r.hideTimer), r.hideTimer = setTimeout(function() {
                e.each(s,
                function(e) {
                    t.removeClass(e, o)
                }),
                t.css(r.viewer, {
                    width: "0"
                })
            },
            i.hideDelay * 1e3))
        },
        resetPostion: function() {
            var n = this.triggers[this.config.idx],
            r = t.offset(n).top,
            i = t.offset(this.container),
            o = t.height(n),
            u = t.height(this.viewer),
            a = t.width(n),
            f = t.viewportHeight(),
            l = t.scrollTop(),
            c = r - l,
            p = f - u - c,
            d = f - c,
            m = r - i.top;
            if (p <= 0) {
                p = Math.abs(p);
                var g = 20;
                if (d > o) {
                    var y = d - o;
                    y > g ? m = m - p - g + 7 : m -= p
                } else m = m - p + g + d + 20
            }
            m < 30 && (m = 0);
            var b = e.UA.ie ? 0 : h; ! s && v ? new e.Anim(this.viewer, {
                top: m - b + "px"
            },
            .3, "easeOutStrong").run() : (this.viewer.style.top = m - b + "px", v = !0)
        },
        _load: function(n) {
            var r = this,
            i = r.config;
            e.IO.get(n, s ? {
                t: +new Date
            }: {},
            function(e) {
                if (!e) return;
                newViewer = t.create(e),
                t.html(r.viewer, t.html(newViewer)),
                r.subViews = t.query(i.subViews, r.viewer),
                r.shadow = t.get(".shadow", r.viewer),
                r.isDataReady = !0,
                r.status == "visible" && (d = !1, r.show())
            },
            "text")
        },
        _init: function() {
            var r = this,
            i = r.config;
            e.each(r.triggers,
            function(e) {
                n.on(e, a,
                function(n) {
                    n.halt();
                    var s = g(r.triggers, e);
                    i.idx = s,
                    r.status = "visible",
                    r.viewer || (!i.viewer && i.lazyload ? (r.viewer = t.create('<div id="J_SubCategory" class="subCategory"><div class="shadow"></div><div class="subView j_SubView" style="height:520px; text-align:center; line-height:520px;">loading...</div></div>'), r.container.appendChild(r.viewer), r.subViews = t.query(i.subViews, r.viewer), r.isDataReady = !1, r._load(i.dataUrl)) : (r.viewer = i.viewer, r.subViews = t.query(i.subViews, r.viewer), r.isDataReady = !0)),
                    r.show()
                }),
                n.on(e, f,
                function(e) {
                    r.status = "triggerLeave"
                })
            }),
            n.on(r.container, f,
            function(e) {
                r.hide(i.idx)
            })
        }
    }),
    KISSY.Category = m,
    m
}),
function(e) {
    var e = KISSY,
    t = e.DOM,
    n = e.Event,
    r = e.UA;
    e.use("category/category",
    function(e, t) {
        new e.Category("#J_Category", {
            viewId: "#J_SubCategory",
            subViews: ".j_SubView",
            triggers: ".j_MenuItem"
        })
    }),
    e.use("switchable,anim,datalazyload,sizzle",
    function(e, r, i, s) {
        var o = e.Slide || r.Slide;
        new o(".market-flash", {
            navCls: "head-slide-nav",
            contentCls: "head-slide-con",
            viewSize: 950,
            autoplay: !0,
            effect: "scrollx",
            easing: "easeOutStrong"
        }),
        e.ready(function(e) {
            var r = t.query("span", t.get(".fixed-recAd"));
            e.DataLazyload({
                mod: "manual",
                diff: 50
            }),
            n.on(r, "mouseover",
            function(t) {
                e.each(r,
                function(n) {
                    if (t.target === n) return;
                    e.Anim(n, {
                        opacity: "0.3",
                        filter: "alpha(opacity=30)"
                    },
                    .1, undefined).run()
                })
            }),
            n.on(r, "mouseout",
            function(t) {
                e.each(r,
                function(n) {
                    if (t.target === n) return;
                    e.Anim(n, {
                        opacity: "0",
                        filter: "alpha(opacity=0)"
                    },
                    .1, undefined).run()
                })
            })
        })
    })
} (KISSY);
var S = KISSY,
D = S.DOM;
var J_main = S.one(".main"),
J_hoverTab = S.one("#J_hoverTab");
SuperMarket.Header.onSiteInfo(function() {
    if (SuperMarket.Header.currentSite == 1) {
        J_hoverTab.css({
            display: "block"
        })
    }
});
if (J_hoverTab) {
    S.Event.on("#J_hoverTab", "mouseenter",
    function() {
        D.addClass(this, "hover")
    });
    S.Event.on("#J_hoverTab", "mouseleave",
    function() {
        D.removeClass(this, "hover")
    })
}