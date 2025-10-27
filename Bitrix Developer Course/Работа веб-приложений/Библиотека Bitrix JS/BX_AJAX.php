<?
// подключаем пролог ядра bitrix
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

// устанавливаем заголовок страницы
$APPLICATION->SetTitle("AJAX");

// подключаем ajax
CJSCore::Init(array('ajax'));

// создаем строку
$sidAjax = 'testAjax';

// создаем условие, существует ли $_REQUEST['ajax_form'] и равняется ли sidAjax
if(isset($_REQUEST['ajax_form']) && $_REQUEST['ajax_form'] == $sidAjax){
    // сбрасываем буфер вывода
    $GLOBALS['APPLICATION']->RestartBuffer();
    // преобразуем PHP-массив в формат JSON
    echo CUtil::PhpToJSObject(array(
        'RESULT' => 'HELLO',
        'ERROR' => ''
    ));
    // завершаем выполнение скрипта
    die();
}

?>
<!-- часть верстки -->
<div class="group">
   <div id="block"></div >
   <div id="process">wait ... </div >
</div>

<!-- скрип js -->
<script>
    // устанавливаем переменную BXDEBUG в объекте window со значением true
    window.BXDEBUG = true;

    // создаем функцию DEMOLoad
    function DEMOLoad(){
        // скрываем элемент с id "block"
        BX.hide(BX("block"));
        // показываем элемент с id "process"
        BX.show(BX("process"));
        // загружаем json-объект из заданного url и передаем его обработчику callback
        BX.ajax.loadJSON(
            '<?=$APPLICATION->GetCurPage()?>?ajax_form=<?=$sidAjax?>',
            DEMOResponse
        );
    }

    // создаем функцию DEMOResponse c передачей параметра
    function DEMOResponse (data){
        // выводим данные ответа
        BX.debug('AJAX-DEMOResponse ', data);
        // обновляем содержимое блока с id "block"
        BX("block").innerHTML = data.RESULT;
        // показываем элемент с id "block"
        BX.show(BX("block"));
        // скрываем элемент с id "process"
        BX.hide(BX("process"));

        // вызываем пользовательское событие 'DEMOUpdate' на элементе с id "block"
        BX.onCustomEvent(
            BX(BX("block")),
            'DEMOUpdate'
        );
    }

    // выполнение скрипта только после полной подготовки страницы
    BX.ready(function(){

        // добавления обработчика события 'DEMOUpdate' на элемент с id "block" (перезагружаем страницу при срабатывании события)
        /*
        BX.addCustomEvent(BX("block"), 'DEMOUpdate', function(){
            window.location.href = window.location.href;
        });
        */

        // скрываем элемент с id "block"
        BX.hide(BX("block"));
        // скрываем элемент с id "process"
        BX.hide(BX("process"));

        // устанавливаем обработчик события "click" на дочерние элементы "document.body" с классом "css_ajax"
        BX.bindDelegate(
            document.body, 'click', {className: 'css_ajax' },
            function(e){

                // создаем условие, если событие не передано
                if(!e){
                    // присваиваем значение глобального события
                    e = window.event;
                }

                // вызываем функцию DEMOLoad
                DEMOLoad();

                // отменяем действие по умолчанию для элемента
                return BX.PreventDefault(e);
            }
        );
    
    });

</script>

<!-- часть верстки -->
<div class="css_ajax">click Me</div>

<?
//подключаем эпилог ядра bitrix
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>