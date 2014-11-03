<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>This is the master</title>
    </head>
    <body>
        <div style="float:left">
            <h1>Div left</h1>
            <?php foreach ($_htpl['pages'] as $_htpl['pKey'] => $_htpl['page']){  ?>
            <div class="post">
                <h1>
                    <?php echo $_htpl['page']['title'];?>
                </h1>
                <div>
                    <p class="teaser">
                        <?php echo \Webiny\Htpl\Modifiers\DatePack::caseMod(\Webiny\Htpl\Modifiers\DatePack::wordTrim((!empty($_htpl['page']['teaser']) ? $_htpl['page']['teaser'] : 'content'), '230'), 'upper');?>
                    </p>
                    <div class="meta">
                        <span class="author">
                            <?php echo $_htpl['page']['author']['name'];?>
                        </span>
                        <span class="date">
                            <?php echo \Webiny\Htpl\Modifiers\DatePack::timeAgo($_htpl['page']['publishedDate']);?>
                        </span>
                    </div>
                    <?php if (($_htpl['name']=='pero') && ($_htpl['age']>=25 && $_htpl['age']<=100) && ($_htpl['title']!=$_htpl['someTitleVar'])) { ?>
                    <div class="img">
                        <?php  if(\Webiny\Htpl\DeviceDetection\DeviceDetection::isDesktop()){
                        echo \Webiny\Htpl\Functions\WImage::getImage("page.image.org", "800px", "450px", "cropAndResize");
                        }
                        if(\Webiny\Htpl\DeviceDetection\DeviceDetection::isTablet()){
                        echo \Webiny\Htpl\Functions\WImage::getImage("page.image.org", "600px", "300px", "resize", "#fffff");
                        }
                        if(\Webiny\Htpl\DeviceDetection\DeviceDetection::isMobile()){
                        echo \Webiny\Htpl\Functions\WImage::getImage("page.image.mobile", "350px", "200px", "crop", "#fffff");
                        }
                        ?>
                        <?php  echo \Webiny\Htpl\Functions\WImage::getImage("page.image.org", "800px", "450px", "cropAndResize"); ?>
                    </div>
                    <?php } ?>
                    <?php if (($_htpl['name']==$_htpl['pero']) || ($_htpl['label']>=10)) { ?>
                    <?php foreach ($_htpl['labels'] as $_htpl['label']){  ?>
                    <span class="label">
                        <a href="<?php echo $_htpl['label']['href'];?>">
                        <?php echo $_htpl['label']['name'];?>
                    </a>
                </span>
                <?php } ?>
                <?php } ?>
            </div>
        </div>
        <?php } ?>
    </div>
    <div style="float:left">
        <h1>Div right</h1>
        <div>content-right: from 2col/simple</div>
    </div>
    <div class="bla">content-middle: from-2col</div>
    <div>footer:from master</div>
    <script type="text/javascript" src="/minified/htpl.e3b03ac7068e21eae95633dcfa0475e4.min.js">
    </script>
</body>
</html> 