<?php

    use apilib\ReqURI;
    use apilib\AuthManager;

    use apidb\ApiDb;



    if (ReqURI::isFileName()) {
        // INCLUDE found FILE
        include_once(ReqURI::getFileName());
    }
    else {
        // API reference not found
        include_once(ReqURI::get404FileName());
    } // else



?>
