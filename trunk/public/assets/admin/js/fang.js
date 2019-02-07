function getget(){
    var elements = new Array();
    for(var i = 0;i<arguments.length;i++)
    {
        var element = arguments[i];
        if(typeof element == 'string')
            element = document.getElementById(element);
        if(arguments.length == 1)
            return element;
        elements.push(element);
    }
    return elements;
}
function removeChild(parent){
	//移除所有子节点
	if(!(parent = getget(parent))) { return false;}
    while(parent.firstChild)
    {
        parent.firstChild.parentNode.removeChild(parent.firstChild);
    }
    return parent;
}
var type = 0;
function choosemodel(type){
	if(type<1||type>10)
	{
		return false;
	}
	this.type = type;
	stylearr = new Array();
    stylearr[1] = '<div class="pw470 ph351 gl_container"></div><div class="pw470 ph351 gl_container"></div><div class="pw944 ph351 gl_container"></div>';
    stylearr[2] = '<div class="pw944 ph470 gl_container"></div><div class="pw312 ph232 gl_container"></div><div class="pw312 ph232 gl_container"></div><div class="pw312 ph232 gl_container"></div>';
    stylearr[3] = '<div class="pw470 ph351 gl_container"></div><div class="pw470 ph351 gl_container"></div><div class="pw470 ph351 gl_container"></div><div class="pw470 ph351 gl_container"></div>';
    stylearr[4] = '<div class="fl mbnone clearfix"><div class="pw470 ph470 gl_container"></div><div class="pw470 ph232 gl_container"></div></div><div class="fl mbnone clearfix"><div class="pw470 ph232 gl_container"></div><div class="pw470 ph470 gl_container"></div></div>';
    stylearr[5] = '<div class="pw312 ph232 gl_container"></div><div class="pw312 ph232 gl_container"></div><div class="pw312 ph232 gl_container"></div><div class="pw944 ph470 gl_container"></div>';
    stylearr[6] = '<div class="pw312 ph706 gl_container"></div><div class="pw312 ph706 gl_container"></div><div class="pw312 ph706 gl_container"></div>';
    stylearr[7] = '<div class="pw312 ph706 gl_container"></div><div class="pw628 ph351 gl_container"></div><div class="pw628 ph351 gl_container"></div>';
    stylearr[8] = '<div class="pw628 ph706 gl_container"></div><div class="pw312 ph233 gl_container "></div><div class="pw312 ph233 gl_container "></div><div class="pw312 ph232 gl_container "></div>';
    stylearr[9] = '<div class="pw628 ph706 gl_container"></div><div class="pw312 ph351 gl_container "></div><div class="pw312 ph351 gl_container "></div>';
    stylearr[10] = '<div class="pw470 ph706 gl_container"></div><div class="pw470 ph351 gl_container "></div><div class="pw470 ph351 gl_container "></div>';
    removeChild('container');
    //$("#container").html('');
    $("#container").append(stylearr[type]);
    $(".gl_container").each(function(){
    	var ss= $('<div class="gl_control"><span>缩放</span><div class="gzoomSlider ui-slider ui-slider-horizontal ui-widget ui-widget-content ui-corner-all" style="width: 180px;background-color:#ffffff;" id="slider"><div class="gl_jind" style="width:50%;"></div><a href="#" class="ui-slider-handle ui-state-default ui-corner-all" ></a></div></div>');
    	// var ss= $('<div class="gzoomSlider ui-slider ui-slider-horizontal ui-widget ui-widget-content ui-corner-all" style="width: '+($(this).width()-30)+'px;background-color:#ffffff;margin-left:15px;" id="slider"><a href="#" class="ui-slider-handle ui-state-default ui-corner-all" ></a></div>');
    	$(this).append(ss);

    	$('.gzoomSlider').slider({
    	value:100,min: 0,max: 200,step: 1,	 
		slide: function(event,ui){
			if($(this).parent().parent().children('img'))
			{
				$(this).parent().find('.gl_jind').width(ui.value/2+'%')
				$(this).parent().parent().children('img').width(ui.value+'%');
			}
		}
		});
    });
    init();
}
var topval=0;
var leftval=0;
function gettop(targetw,targeth,sourcew,sourceh)
{
	if( sourcew<=targetw )
	{
		if(sourceh<=targeth)
		{	//横纵居中
			leftval = calu(targetw,sourcew);
			topval = calu(targeth,sourceh);
		}
		else if(sourceh>targeth)
		{   //横向居中  纵向截取
			leftval= calu(targetw,sourcew);
			topval = calu(targeth,sourceh);
		}
	}
	else if(sourcew>targetw)
	{
		if(sourceh<=targeth)
		{   //横向截取  纵向截取居中
			leftval = calu(targetw,sourcew);
			topval = calu(sourceh,targeth);
		}
		else if(sourceh>targeth)
		{	//横纵截取中间位置
			leftval = calu(targetw,sourcew);
			topval = calu(targeth,sourceh);
		}
	}
}
function init()
{
	$( ".gl_dragsource" ).draggable({
		helper:"clone",scroll: false
		});
	$(".gl_container").droppable({
	drop:function(event,ui){
		if(event.stopPropagation)	
		        event.stopPropagation();
		    else
		        event.cancelBubble = true;
		source = ui.draggable.children();
		sourcename=parseInt(source.attr('name'));
		
		targetw=parseInt($(this).css('width'));
		targeth=parseInt($(this).css('height'));
//		sourcew=parseInt(source.attr('w'));
//		sourceh=parseInt(source.attr('h'));
//		gettop(targetw,targeth,sourcew,sourceh);
		var dd = $("<img class='cimg ui-draggable' name='"+sourcename+"' src='"+source.attr('src')+"' style='width:100%;top:-17px;'/>");
		if($(this).children('img').attr('name') && sourcename && source.attr('src'))
		{
			$(this).children('img').attr('name',sourcename);
			$(this).children('img').attr('src',source.attr('src'));
		}
		else
		{
			if(!sourcename || !source.attr('src')){}
			else
			{
				$(this).append(dd);
			}
		}
		$(dd).draggable();
}
});
}

function calu(a,b)
{
	return (a-b)/2;
}

function uploadpjpic()
{
	taotype = $('#pictypexts').val();
	if(!taotype)
	{
		return false;
	}
	var r=confirm("确认生成该图片到"+$('#pictypexts option:selected').text()+"套吗?")
	if (r!=true)
	{
		return false;
	}
	datas = {"type":this.type};
	var i = 0;
	del = new Array();
	$('.cimg').each(function(index){
		del[i] = $(this).attr('name');
		datas[i+'_p'] = $(this).attr('src').substring(38);
		datas[i+'_pw'] = parseInt($(this).css('left'));
		datas[i+'_ph'] = parseInt($(this).css('top'));
		datas[i+'_w'] = parseInt($(this).width());
		datas[i+'_h'] = parseInt($(this).height());
		i++;
		});
    if(i<1)
    {
        alert("请拼图后上传");return false;
    }
    //添加loading
    $(".loading").show();
    var sync_weibo = 0;
    if($('#sync_weibo').is(':checked')){
        sync_weibo = 1;
    }
	$.ajax( {
		type : "POST",
		url : './manage.php?c=pichouse&a=createpic&tp='+taotype+'&hid='+"135882"+'&search_city='+"anshan"+'&hname='+$.trim($('#hid :selected', parent.document).text())+'&sync_weibo='+sync_weibo,
		data : datas,
		success : function(res) {
		if (res) {
			alert('上传成功');
			for (j in del )
			{
				$(".apf img[name="+del[j]+"]").parent().remove();
			}
		}
        $(".loading").hide();
	}
	});
}
function uploadpic()
{
    var r=confirm("确定要上传其他所有未拼图图片吗？")
	if (r!=true)
	{
		return false;
	}
    //添加loading
    $(".loading").show();
    var sync_weibo = 0;
    if($('#sync_weibo').is(':checked')){
        sync_weibo = 1;
    }
	$.ajax( {
		type : "POST",
		url : './manage.php?c=pichouse&a=uppic'+'&hid='+"135882"+'&search_city='+"anshan"+'&hname='+$.trim($('#hid :selected', parent.document).text())+'&sync_weibo='+sync_weibo,
		data : '',
		success : function(res) {
		if (res) {
            $(".loading").hide();
			alert('上传完毕');
            $("#ptpic", parent.document).attr('src',$("#ptpic", parent.document).attr('src'));
		}
	}
	});
}
