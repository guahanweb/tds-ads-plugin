(function ($) {
    $(document).ready(function () {
        init();
    });

    var CampaignAds = (function (campaigns) {
        // Prep by making sure we have integers in all the campaigns
        Object.keys(campaigns).forEach(function (k) {
            campaigns[k] = campaigns[k].map(function (a) {
                return parseInt(a);
            });
        });

        return {
            getAd: function (cid, aid) {
                var c = getCampaign(cid);
                var i = c.indexOf(aid);
                if (i > -1) {
                    return c.splice(i, 1);
                }
                return null;
            },

            getRandomAd: function (cid) {
                var c = getCampaign(cid);
                if (c.length > 0) {
                    var i = Math.floor(Math.random() * c.length);
                    return c.splice(i, 1);
                }
                return null;
            }
        };

        function getCampaign(cid) {
            if (typeof campaigns[cid] === 'undefined') {
                return [];
            }
            return campaigns[cid];
        }
    })(TDS_CAMPAIGN_ADS);

    // We only want to show each ad once per campaign
    var Slot = function (el) {
        this.$el = $(el);
        this.campaign_id = this.$el.data('tds-campaign');
        this.random = this.$el.data('tds-ad-random');
        this.ad_id = (this.random) ? null : this.$el.data('tds-ad-id');
    };

    Slot.prototype.load = function () {
        var aid = this.random ? 
            CampaignAds.getRandomAd(this.campaign_id) :
            CampaignAds.getAd(this.campaign_id, this.ad_id);

        // We have to be sure this ad hasn't yet been used IN THIS CAMPAIGN
        if (null !== aid) {
            var $ad = $('#tds-ad-' + aid).children().clone(true, true);
            if (!!$ad) {
                this.$el.append($ad);
            }
        }
    };

    function init() {
        $('div[data-tds-campaign]').each(function (i, el) {
            var slot = new Slot(el);
            slot.load();
        });
    }
})(jQuery);
