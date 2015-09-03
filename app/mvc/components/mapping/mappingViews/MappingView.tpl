<div id="contentWrapper" class="container">
    
    <div class="text-center" id="icon">
        <h3><i class="fa fa-link"></i> {getString key="mapping.title"}</h3>
    </div>

    <div class="text-center">
        <a href="{route url="mapping/MappingController" map="map1" vars=['x' => 1, 'y' => 2, 'z' => 3]}">x=1, y=2, z=3</a> |
        <a href="{route url="mapping/MappingController" map="map2" vars=['a' => 'xda', 'b' => 'cnn']}">a=xda, b=cnn</a>
        <br>
        <br>
        x = '{$request->get('x')}'<br>
        y = '{$request->get('y')}'<br>
        z = '{$request->get('z')}'<br>
        a = '{$request->get('a')}'<br>
        b = '{$request->get('b')}'<br>
    </div>
    
</div>