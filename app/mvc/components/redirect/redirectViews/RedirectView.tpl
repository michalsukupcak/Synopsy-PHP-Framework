<div id="contentWrapper" class="container">
    
    <div class="text-center" id="icon">
        <h3><i class="fa fa-mail-forward"></i> {getString key="redirect.title"}</h3>
    </div>

    <div class="text-center">
        <a href="{route url="redirect/RedirectController:r404"}">{getString key="redirect.404"}</a> |
        <a href="{route url="redirect/RedirectController:r500"}">{getString key="redirect.500"}</a> |
        <a href="{route url="redirect/RedirectController:rHome"}">{getString key="redirect.home"}</a>
    </div>
    
</div>