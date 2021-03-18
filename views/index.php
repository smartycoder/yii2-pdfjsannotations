<?php

/* @var $this yii\web\View */
/* @var $pdfFilePath string */

$js = <<<JS
ELEMENTS = {};
PAGE = 0;
var scale = 0;
var pdf = null;


function initPdf(scale){
    pdf = new PDFAnnotate("pdf-container", "$pdfFilePath", {
      onPageUpdated(page, oldData, newData) {
          PAGE = page;
          console.log(page, oldData, newData);
      },
      ready() {
        console.log("Plugin initialized successfully");
      },
      scale: scale,
      pageImageCompression: 'MEDIUM', // FAST, MEDIUM, SLOW(Helps to control the new PDF file size)
    });
}

initPdf(1.33);

$("select.scale").change(function (){
    $('#pdf-container').html('');
    scale = parseFloat($("select.scale :selected").val());
    initPdf(scale); 
});


function initCanvasEvents(canvas, currentPage){
     $.each(ELEMENTS, function(i,e){
         if(currentPage+1 == e.page){
            rect = new fabric.Rect({
                left: (e.left / e.scale) * scale,
                top: (e.top / e.scale) * scale,
                originX: e.originX,
                originY: e.originY,
                x: e.x,
                y: e.y,
                width: ((e.width * e.scaleX) / e.scale) * scale - 2,
                height: ((e.height * e.scaleY) / e.scale) * scale - 2,
                angle: 0,
                scaleX: 1,
                zoomX: 1,
                scaleY: 1,
                zoomY: 1,
                hasControls: false,
                selectable: false,
                stroke: 'rgba(255,0,0,1)',
                strokeWidth: 2,
                rx:2,
                ry:2,
                fill: 'rgba(0,0,0,0)',
                transparentCorners: false,
                hasRotatingPoint : false,
            });
            
            rows = e._objects[1].text.split('\\r\\n').length + 1;
            var text = new fabric.Textbox(e._objects[1].text, {
                left: rect.left + 5,
                
                top: rect.top + 5 + (((e.height * e.scaleY) / e.scale) * scale - 2) - (10 * scale * rows) - 4,
                fontSize: 10 *scale,
                hasControls: false,
                selectable: false,
                width: ((e.width * e.scaleX) / e.scale) * scale - 2,
                height: ((e.height * e.scaleY) / e.scale) * scale - 2,
                
                fill: '#000000'
            });
            canvas.add(rect);
            canvas.add(text);
        
            var group = new fabric.Group([rect, text]);
            group.selectable = false;
            group.hasControls = true;
            group.lockScalingFlip = true;
            
            group.page = e.page;
            group.scale = scale;
            group.uuid = e.uuid;
            canvas.add(group);
            
            ELEMENTS[i] = group;
         }
    });
    canvas.renderAll();
    hideRotatingPointsForAllObjects(canvas);
    
    var rect, isDown, origX, origY, isDragged;
    
    canvas.on('mouse:down', function(o){
        if(o.target == null){
            isDown = true;
            var pointer = canvas.getPointer(o.e);
            origX = pointer.x;
            origY = pointer.y;
            rect = new fabric.Rect({
                left: origX,
                top: origY,
                originX: 'left',
                originY: 'top',
                width: pointer.x-origX,
                height: pointer.y-origY,
                angle: 0,              
                hasControls: false,
                selectable: false,
                stroke: 'rgba(255,0,0,1)',
                strokeWidth: 2,
                rx:2,
                ry:2,
                fill: 'rgba(0,0,0,0)',
                transparentCorners: false,
                hasRotatingPoint : false,
            });
            
            canvas.add(rect);
        }
        else{
            isDown = false;
            //Programmatically activate group when clicking on them
            if(o.target.type == "group"){
                id = canvas.getObjects().indexOf(o.target);
                canvas.setActiveObject(canvas.item(id));
            } else if(o.target.type == "textbox"){
                id = canvas.getObjects().indexOf(o.target.group);
                canvas.setActiveObject(canvas.item(id));
            }
        }
    });
    
    canvas.on('object:moving', function(o){
        preventDragOffCanvas(o);
    });
    canvas.on('object:moved', function(o){
        updateRecipientsTags();
    });
    
    
    canvas.on('object:scaling', function(o){
        preventScalingUnderMinSize(o);
    }); 
    
    canvas.on('object:scaled', function(o){
        preventScalingUnderMinSize(o);
        updateRecipientsTags();
        // resizeAndRemoveScale(o);
    });
    
    canvas.on('mouse:move', function(o){
        if (!isDown) return;
        var pointer = canvas.getPointer(o.e);
        
        if(origX>pointer.x){
            rect.set({ left: Math.abs(pointer.x) });
        }
        if(origY>pointer.y){
            rect.set({ top: Math.abs(pointer.y) });
        }
        
        rect.set({ width: Math.abs(origX - pointer.x) });
        rect.set({ height: Math.abs(origY - pointer.y) });
        
        if(rect.width > 10 && rect.height > 10){
            isDragged = true;
        }
        
        
        canvas.renderAll();
    });
    
    canvas.on('mouse:up', function(o){
        if(isDown && isDragged){
            USER_DIALOG.dialog();
0
			if (USER_DIALOG.dialog("isOpen")=="true") {
                return;
            }		
			
            preventDragOffCanvas(rect);
			resizeRect =  getTagFromRectangle(rect, "test");
			
            var text = new fabric.Textbox(" ", {
                left: rect.left + 5,
                top: rect.top + 5,
                fontSize: 10 * scale,
                hasControls: false,
                selectable: false,
                width: rect.width-4,
                height: rect.height-4,
                fill: '#000000'
            });
        
            var group = new fabric.Group([rect, text]);
            group.selectable = false;
            group.hasControls = true;
            group.lockScalingFlip = true;
            canvas.add(group);
			var uuid = new Date().getTime();
            USER_DIALOG.tag = getTagFromRectangle(group, uuid);
            
            $('#first_name, #last_name, #phone, #email').focus().val('').removeClass('valid').removeClass('invalid');
            $("label[for='first_name'], label[for='last_name']").removeClass('active');

            console.log(USER_DIALOG.tag);

            rect.uuid = uuid;
            rect.page = PAGE;
            rect.scale = scale;
            
            group.uuid = uuid;
            group.page = PAGE;
            group.scale = scale;
            ELEMENTS[uuid] = group;
            USER_DIALOG.rect = group;

            USER_DIALOG.dialog({ title: DEFAULT_DIALOG_TITLE, draggable: false });
            USER_DIALOG.dialog( "open" );    
            
            // Hides rotating point
            hideRotatingPointsForAllObjects(canvas);
        } else if (isDown && !isDragged){
            canvas.remove(rect);
        }
        isDown = false;
        isDragged = false;
    });
    
    //in progress
//    function resizeAndRemoveScale(e){
//         var o = e.target;
//         var scaleX = o.scaleX;
//         var scaleY = o.scaleY;
//         o.set('width', o.width * scaleX);
//         o.set('height', o.height * scaleY);
//         o.set('scaleX', 1);
//         o.set('scaleY', 1);
//         o.set('zoomX', 1);
//         o.set('zoomY', 1);
//        
//         $.each(o._objects, function(index, element){
//             var width_change =(element.width - (element.width * scaleX))/2;
//             var height_change = (element.height - (element.height * scaleY))/2;
//             element.set('width', element.width * scaleX);
//             element.set('height', element.height * scaleY);
//             element.set('top', element.top + height_change);
//             element.set('left', element.left + width_change);
//             element.set('scaleX', 1);
//             element.set('scaleY', 1);
//             element.set('zoomX', 1);
//             element.set('zoomY', 1);
//             if(element.type == "textBox"){
//                 debugger;
//                 rows = element.text.split('\\r\\n').length + 1;
//                 element.set('top', element.top + 5 + (((o.height * o.scaleY) / o.scale) * scale - 2) - (10 * scale * rows) - 4,);
//             }
//         });
//    }
    
    function preventScalingUnderMinSize(e){
        var target = e;
        if(e.target){
            target = e.target;
            if(e.target.group){
                target = e.target.group;
            }
        }
        var minHeight = 75;
        var minWidth = 100;
        var height = canvas.height / scale;
        var rect_width = (target.width * target.scaleX) / scale;
        var rect_height = (target.height * target.scaleY) / scale;
        if(rect_width <= minWidth)
        {
            target.set({
                scaleX: target.old_scaleX,       
                });
        }else{
           target.old_scaleX = target.scaleX;
        }

        if(rect_height <= minHeight)
        {
            target.set({
                scaleY: target.old_scaleY,
                });
        }else{
           target.old_scaleY = target.scaleY;
        }
    }
    
    function preventDragOffCanvas(e){
        var target = e;
        if(e.target){
            target = e.target;
        }
       var height = target.height * target.scaleY
            , width = target.width * target.scaleX
            , top = target.top
            , left = target.left
            , rightBound = canvas.width
            , bottomBound = canvas.height
            , modified = false;
        
        // don't move off top
        if(top < 0){
            top = 0;
            modified = true;
        }
        // don't move off bottom
        if(top + height > bottomBound){
            top = bottomBound - height;
            modified = true;
        }
        // don't move off left
        if(left < 0){
            left = 0;
            modified = true;
        }
        // don't move off right
        if(left + width > rightBound){
            left = rightBound - width;
            modified = true;
        }
    
        if(modified){
            target.left=left;
            target.top=top;
            return true;
        }
        return false;
    }
    
    function updateRecipientsTags(){
        $.each(RECIPIENTS, function(key, recipient){
            rectangle = ELEMENTS[recipient.uuid];
            recipient.fld = getTagFromRectangle(rectangle, recipient.uuid);
        });
    }
    
    function getTagFromRectangle(rect, uuid){
        var height = canvas.height / scale;
        var rect_width = (rect.width * rect.scaleX) / scale;
        var rect_height = (rect.height * rect.scaleY) / scale;
        var rect_left = rect.left / scale;
        var y = height - (rect.top / scale) - rect_height;
        if (rect_width < 100){
            rect.set('width', 100 * scale);
            rect_width = (rect.width * rect.scaleX);
        }
        if (rect_height < 75){
            rect.set('height', 75* scale);
            rect_height = (rect.height * rect.scaleY);
        }
        return {
            x: rect_left,
            y: y,
            width: rect_width,
            height: rect_height,
            page: PAGE,
            uuid: uuid,
            page_height: height
        };
    }
    
        
}

function hideRotatingPointsForAllObjects(canvas){
    $.each(canvas._objects, function(indx, item){
        item["setControlVisible"]("mtr", false);
    });
    canvas.renderAll();
}

JS;

$this->registerJs($js, \yii\web\View::POS_END);

\smartysoft\PdfjsAnnotationsAssets::register($this);

?>
<head>
    <title></title>
</head>
<body>
<div id="outerContainer">
    <div id="pdf-container"></div>
</div>

<div class="modal fade" id="dataModal" tabindex="-1" role="dialog" aria-labelledby="dataModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dataModalLabel">PDF annotation data</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
				<pre class="prettyprint lang-json linenums">
				</pre>
            </div>
        </div>
    </div>
</div>
</body>
