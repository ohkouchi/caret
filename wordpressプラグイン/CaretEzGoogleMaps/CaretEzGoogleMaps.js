(function()
{
	tinymce.create('tinymce.plugins.CaretEzGoogleMaps',
	{
		getInfo : function()
		{
			return {
				longname : 'CaretEzGoogleMaps',
				author : 'Caret Inc.',
				authorurl : 'http://www.ca-ret.co.jp/',
				infourl : 'http://www.ca-ret.co.jp/WordPress/',
				version : "1.0.0"
			};
		},
		init : function(ed, url)
		{
			var t = this;
			t.editor = ed;
			ed.addCommand('GoogleMaps', function()
			{
				var exist = ed.selection.getContent();
				var str = t._GoogleMapsTag(exist);
				ed.execCommand('mceInsertContent', false, str);
			});
			ed.addButton('GoogleMaps',
			{
				title : 'GoogleMaps',
				cmd : 'GoogleMaps',
				image : url + '/button.png'
			});
		},
		_GoogleMapsTag : function(d)
		{
			str = '{GoogleMaps}'+d+'{/GoogleMaps}';
			return str;
		}
	});
    tinymce.PluginManager.add('CaretEzGoogleMaps', tinymce.plugins.CaretEzGoogleMaps);
})();
