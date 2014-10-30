<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>This is the master</title>
    </head>
    <body>
        <div style="float:left">
            <h1>Div left</h1>
            <!--<?php foreach ($_htpl["pages"] as $_htpl["page"]){  ?>-->
            <div class="post">
                <h1>
                    <?php echo $_htpl["title"];?>
                </h1>
                <div>
                    <p class="teaser">
                        <?php echo \Webiny\Htpl\Modifiers\DatePack::caseMod(\Webiny\Htpl\Modifiers\DatePack::wordTrim((!empty($_htpl["teaser"]) ? $_htpl["teaser"] : "content"), "230"), "upper");?>
                    </p>
                    <div class="meta">
                        <span class="author">
                            <?php echo \Webiny\Htpl\Modifiers\DatePack::caseMod(\Webiny\Htpl\Modifiers\DatePack::wordTrim((!empty($_htpl["author.name"]) ? $_htpl["author.name"] : "content"), "230"), "upper");?>
                        </span>
                        <span class="date">
                            <?php echo \Webiny\Htpl\Modifiers\DatePack::timeAgo((!empty($_htpl["publisedDate"]) ? $_htpl["publisedDate"] : "content"));?>
                        </span>
                    </div>
                    <div class="img">
                        <img src="default.img" w-src="image" width="400" height="250"/>
                    </div>
                    <!--<?php foreach ($_htpl["labels"] as $_htpl["page"]){  ?>-->
                    <span class="label">
                        <a href="#" w-href="label.href">label.name</a>
                    </span>
                    <!--<?php } ?>-->
                </div>
            </div>
            <!--<?php } ?>-->
        </div>
        <div style="float:left">
            <h1>Div right</h1>
            <div>content-right: from 2col/simple</div>
        </div>
        <div class="bla">content-middle: from-2col</div>
        <div>footer:from master</div>
    </body>
</html> 