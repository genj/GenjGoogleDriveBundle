var fullpageslideshow={
	imagesarr:[], layers:null, thumbs:null, layerorder:{fg:1, bg:0}, curimage:0, previmage:0,
	setting:{fadeduration:1000, autorotate:{enabled:true, pause:3000}, thumbdimensions:[50,50], sortby:'date', thumbdir:''},

	signalcomplete:function(){
		var slideshow=this, imagesarr=this.imagesarr, thumbs=this.thumbs, templayer=this.templayer, setting=this.setting, imagecount=0
		function createthumbnail(i, imageref, thumbref){
			var thumbimgsrc=(setting.imagesobj.baseurl)? setting.thumbdir+setting.imagesobj.images[i][1] : imageref.src
			thumbref.html('<img src="'+thumbimgsrc+'" style="width:'+setting.thumbdimensions[0]+'px;height:'+setting.thumbdimensions[1]+'px" />')
			thumbref.find('img:eq(0)').css({opacity:0.3})
		}
		function process(i, imageref, thumbref){
			templayer.append('<img src="'+imageref.src+'" />')
			var tempimage=templayer.find('img:last')
			imageref._specs={w:tempimage.width(), h:tempimage.height()}
			thumbref.find('img:eq(0)').css({opacity:1})
			if (i==slideshow.curimage){ //if first image has loaded
				slideshow.changeimage(slideshow.curimage, true)
				if (setting.autorotate.enabled)
					setting.rotatetimer=setInterval(function(){slideshow.cycleimage()}, setting.autorotate.pause)
			}
			imagecount++
			if (imagecount==imagesarr.length){
				templayer.remove()
			}
		}
		for (var i=0; i<imagesarr.length; i++){
			createthumbnail(i, imagesarr[i], thumbs.eq(i))
			if (imagesarr[i].complete){
				process(i, imagesarr[i], thumbs.eq(i))
			}
			else{
				imagesarr[i].onload=function(){
					var pos=this._index
					process(pos, this, thumbs.eq(pos))
				}
			}
		}
	},

	scaleimage:function(imageref){
		var od=imageref._specs, windoww=$(window).width(), windowh=$(window).height()
		var neww=(od.w>od.h)? windoww : Math.round(windowh*od.w/od.h)
		var newh=Math.round(neww*od.h/od.w)
		if (neww>windoww){
			neww=windoww
			newh=Math.round(neww*od.h/od.w)
		}
		else if (newh>windowh){
			newh=windowh
			neww=Math.round(newh*od.w/od.h)
		}
		var xpos=(neww>=windoww)? 0 : windoww/2-neww/2
		var ypos=(newh>=windowh)? 0 : windowh/2-newh/2
		return {width:neww+'px', height:newh+'px', left:xpos+'px', top:ypos+'px', position:'absolute'}
	},	

	changeimage:function(i, forcechange){
		var layers=this.layers, imagesarr=this.imagesarr, layerorder=this.layerorder, thumbs=this.thumbs, setting=this.setting
		var fglayer=layers.eq(layerorder.fg), bglayer=layers.eq(layerorder.bg)
		if (!imagesarr[i].complete && typeof forcechange=="undefined")
			return
  		bglayer.stop(true,true).css({opacity:1, zIndex:999}).empty().append($('<img src="'+imagesarr[i].src+'" />').css(this.scaleimage(imagesarr[i]))) //update background layer's image
		fglayer.css({opacity:1, zIndex:1000}).stop(true,true).animate({opacity:0}, setting.fadeduration, function(){
			bglayer.css('z-index', 1000)
			fglayer.css('z-index', 999)
		})
		layerorder.fg=layerorder.fg==1? 0 : 1
		layerorder.bg=layerorder.bg==1? 0 : 1
		this.previmage=this.curimage
		this.curimage=i
		thumbs.eq(this.previmage).find('img:eq(0)').removeClass('selected')
		thumbs.eq(i).find('img:eq(0)').addClass('selected')
	},

	cycleimage:function(){
		var nextimage=(this.curimage<this.imagesarr.length-1)? this.curimage+1 : 0
		this.changeimage(nextimage)
	},

	init:function(options){
		this.setting=$.extend({}, this.setting, options)
		this.setting.autorotate.pause+=this.setting.fadeduration
		var images=options.imagesobj.images || options.imagesobj, imagesarr=this.imagesarr
		images.pop()
		if (options.imagesobj.baseurl){ //if images are auto retrieved using PHP
			this.setting.thumbdir=options.imagesobj.baseurl+this.setting.thumbdir+"/" //augment thumbnail directory with baseurl to form full URL to thumbs dir
			if (options.sortby=="date")
				images.sort(function(a,b){return new Date(b[2])-new Date(a[2])})
			else{
				images.sort(function(a,b){ //sort by file name
					var filea=a[1].toLowerCase(), fileb=b[1].toLowerCase()
					return (filea<fileb)? -1 : (filea>fileb)? 1 : 0
				})
			
			}
		}
		var thumbsarr=['<ul class="fpthumbs" style="z-index:1001">']
		for (var i=0; i<images.length; i++){
			imagesarr[i]=new Image()
			imagesarr[i].src=(options.imagesobj.baseurl)? options.imagesobj.baseurl+images[i][1] : images[i]
			imagesarr[i]._index=i
			thumbsarr.push('<li></li>')
		}
		thumbsarr.push('</ul>')
		jQuery(function($){
			var slideshow=fullpageslideshow
			var layers=$('<div style="position:absolute;left:0;top:0; width:100%; height:100%;overflow:hidden;background:black;" />').clone().andSelf().appendTo(document.body)
			var thumbs=$(thumbsarr.join('')).appendTo(document.body)
			thumbs=thumbs.find('li')
			thumbs.each(function(i){
				var $thumb=$(this)
				this._index=i
				$thumb.css({left: 60*i+20})
				$thumb.click(function(){
					clearTimeout(slideshow.setting.rotatetimer)
					slideshow.changeimage(this._index)
				})
			})
			slideshow.layers=layers
			slideshow.thumbs=thumbs
			slideshow.templayer=$('<div style="position:absolute;left:-5000px;top:-5000px;visibility:hidden" />').appendTo(document.body)
			slideshow.signalcomplete()
			$(window).resize(function(){
				if (imagesarr[slideshow.curimage].complete==true)
					var cssattr=slideshow.scaleimage(imagesarr[slideshow.curimage])
					slideshow.layers.eq(slideshow.layerorder.fg).find('img').css(cssattr)
			})
		})
	}

}

/////////////INITIALIZE SCRIPT BELOW:

fullpageslideshow.init({ //initialize script
	imagesobj: fpslideshowvar, //no need to change. Object variable referencing images as generated inside "fpslideshow.php" 
	thumbdir: 'thumbnails', //sub directory directly below main images directory containing the thumbnail versions. Image names should be same as main images.
	sortby: 'date', //sort by "date" or "filename"
	fadeduration: 1000,
	thumbdimensions:[30,30],
	autorotate:{enabled:true, pause:4000}
})