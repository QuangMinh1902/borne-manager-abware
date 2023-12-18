<?php

namespace App\Controller;

use App\Entity\Catalog;
use App\Entity\Category;
use App\Entity\Content;
use App\Entity\Media;
use App\Entity\CatalogSubCategory;
use App\Entity\SubCategory;
use App\Entity\Releases;

use Symfony\Component\HttpFoundation\Request; 
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CatalogRepository;
use App\Repository\CategoryRepository; 
use App\Repository\CatalogSubCategoryRepository; 
use App\Repository\SubCategoryRepository; 
use App\Repository\ReleaseRepository; 
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use ZipArchive;

class DownloadController extends AbstractController
{
    #[Route('/download', name: 'download')]
    public function index(EntityManagerInterface $manager)
    {
        //pas de timeout
        set_time_limit(0);
        ini_set('memory_limit', '3G');


        $aAllMedia = array();

        // Création du fichier rubrique.xml
        //CategoryRepository

        $category = $manager->getRepository(Category::class)->extractCategoryAndChild();
        if (!$category) throw $this->createNotFoundException('No Category found');

        $zipname=date('Ymd_His');
        $v5uuid = $this->uuidv5('48f9deac-08ce-4af4-ab3b-9831814623b1', $zipname);
        $workDir = '../public/' . $v5uuid;
        if(!mkdir($workDir, 0777)){
            return "1001";
        }

        
        
        // Rubriques.xml

        $idCategory_hold      = '';
        $idSubCategory_hold   = '';
        $aCatalogIdByScat = array();
        

        $strXmlRubrique = '<rubriques>' . "\n";
        foreach($category as $indice => $aCategory){
            

            if  ($idCategory_hold != $aCategory['idCategory']){

                
                if(!empty($idCategory_hold)){
                    $catalogIdList = implode(',', $aCatalogIdByScat);
                    if(!empty($catalogIdList)) $strXmlRubrique .= '<idVideos>' . $catalogIdList . '</idVideos>' . "\n";
                    if(!empty($idSubCategory_hold)) $strXmlRubrique .= '</sous_rubriques>' . "\n";
                    $strXmlRubrique .= '</infos>' . "\n";
                    $aCatalogIdByScat = array();
                }

                $strXmlRubrique .= '<infos>' . "\n";
                $strXmlRubrique .= '<idRubrique>'   . $aCategory['idCategory']  . '</idRubrique>' . "\n";
                $strXmlRubrique .= '<nom_francais>' . $aCategory['category']    . '</nom_francais>' . "\n";
                $strXmlRubrique .= '<nom_anglais>'  . $aCategory['categoryEng'] . '</nom_anglais>' . "\n";
                $strXmlRubrique .= '<nom_espagnol>' . $aCategory['categoryEsp'] . '</nom_espagnol>' . "\n";
                $strXmlRubrique .= '<nom_allemand>' . $aCategory['categoryAll'] . '</nom_allemand>' . "\n";
                $strXmlRubrique .= '<photo>'        . $aCategory['picture']     . '</photo>'        . "\n";

                if($aCategory['picture']) $aAllMedia[] = $aCategory['picture'] ;
                
                //dd($strXmlRubrique);
                $idCategory_hold = $aCategory['idCategory'];
                $idSubCategory_hold = '';
            }
            if  (($idSubCategory_hold != $aCategory['idSubCategory']) &&  isset($aCategory['idSubCategory'])){

                if (!empty($idSubCategory_hold)){
                   $catalogIdList = implode(',', $aCatalogIdByScat);
                   if(!empty($catalogIdList)) $strXmlRubrique .= '<idVideos>' . $catalogIdList . '</idVideos>' . "\n";
                   $strXmlRubrique .= '</sous_rubriques>' . "\n";

                   $aCatalogIdByScat = array();
                   //dd($strXmlRubrique);
                }
                $strXmlRubrique .= '<sous_rubriques>' . "\n";
                $strXmlRubrique .= '<idSousRurique>' . $aCategory['idSubCategory']    . '</idSousRurique>' . "\n";
                $strXmlRubrique .= '<nom_sous_rubrique_francais>' . $aCategory['subCategory']    . '</nom_sous_rubrique_francais>' . "\n";
                $strXmlRubrique .= '<nom_sous_rubrique_anglais>'  . $aCategory['subCategoryEng']    . '</nom_sous_rubrique_anglais>' . "\n";
                $strXmlRubrique .= '<nom_sous_rubrique_espagnol>' . $aCategory['subCategoryEsp']    . '</nom_sous_rubrique_espagnol>' . "\n";
                $strXmlRubrique .= '<nom_sous_rubrique_allemand>' . $aCategory['subCategoryAll']    . '</nom_sous_rubrique_allemand>' . "\n";
                //$strXmlRubrique .= '</sous_rubriques>' . "\n";

                 $idSubCategory_hold = $aCategory['idSubCategory'];
            }

            $aCatalogIdByScat[] = $aCategory['idCatalog'];
    
        }

        if (!empty($idSubCategory_hold)){
           
            $catalogIdList = implode(',', $aCatalogIdByScat);
            if(!empty($catalogIdList)) $strXmlRubrique .= '<idVideos>' . $catalogIdList . '</idVideos>' . "\n";
            if(!empty($idSubCategory_hold)) $strXmlRubrique .= '</sous_rubriques>' . "\n";


            $aCatalogIdByScat = array();
            
        }

        $strXmlRubrique .= '</infos>' . "\n";
        $strXmlRubrique .= '</rubriques>' . "\n";

        $strXmlRubrique = str_replace('&', '&amp;', $strXmlRubrique);
        
        $strXmlRubrique = mb_convert_encoding($strXmlRubrique, 'UTF-8');
        $strXmlRubrique = chr(239) . chr(187) . chr(191) . $strXmlRubrique;
        file_put_contents($workDir . '/rubriques.xml', $strXmlRubrique);

        $strXmlRubrique = '';
        $category = null;

         // Fin de Rubriques.xml

         // VideosInfos.xml 

         $strXmlVideos = '<videos>' . "\n";
         
         $events = $manager->getRepository(Catalog::class)->extractEvents();

         //dd($events);
         //dd($events);
         //if (!$events) throw $this->createNotFoundException('No Category found');
        foreach($events as $aEvent){
            $strXmlVideos .= '<videoInfos>' . "\n";

            $idCatalog = $aEvent['idCatalog'];
            $idCategory = $aEvent['idCategory'];

            // content 
            $contents = $manager->getRepository(Content::class)->findAllByIdCatalog($idCatalog );
            //dd($contents);
            foreach($contents as $content){
                

                switch($content->getType()){
                    case 'description' : 
                        //dd($content->getIdLang());
                        if($content->getIdLang()==1) $infoDescrFR = $this->toLF($content->getContent()); 
                        if($content->getIdLang()==2) $infoDescrENG = $this->toLF($content->getContent());  
                        if($content->getIdLang()==3) $infoDescrESP = $this->toLF($content->getContent());  
                        if($content->getIdLang()==4) $infoDescrALL = $this->toLF($content->getContent());  
                        break;
                    case 'event' : 
                        if($content->getIdLang()==1) $infoEvennDescrFR = $this->toLF($content->getContent()); 
                        if($content->getIdLang()==2) $infoEvennDescrENG = $this->toLF($content->getContent());  
                        if($content->getIdLang()==3) $infoEvennDescrESP = $this->toLF($content->getContent());  
                        if($content->getIdLang()==4) $infoEvennDescrALL = $this->toLF($content->getContent());  
                        break;
                    case 'information' : 
                        if($content->getIdLang()==1) $infoPratiqueDescrFR  = $this->toLF($content->getContent()); 
                        if($content->getIdLang()==2) $infoPratiqueDescrENG = $this->toLF($content->getContent());  
                        if($content->getIdLang()==3) $infoPratiqueDescrESP = $this->toLF($content->getContent());  
                        if($content->getIdLang()==4) $infoPratiqueDescrALL = $this->toLF($content->getContent());  
                        break;
                    
                    case 'title' : 
                        if($content->getIdLang()==1) $titreVideoFR = $this->toLF($content->getContent()); 
                        if($content->getIdLang()==2) $titreVideoENG = $this->toLF($content->getContent());  
                        if($content->getIdLang()==3) $titreVideoESP = $this->toLF($content->getContent());  
                        if($content->getIdLang()==4) $titreVideoALL = $this->toLF($content->getContent());  
                        break;

                    case 'subtitle' : 
                        if($content->getIdLang()==1) $SousTitreVideoFR = $this->toLF($content->getContent()); 
                        if($content->getIdLang()==2) $SousTitreVideoENG = $this->toLF($content->getContent());  
                        if($content->getIdLang()==3) $SousTitreVideoESP = $this->toLF($content->getContent());  
                        if($content->getIdLang()==4) $SousTitreVideoALL = $this->toLF($content->getContent());  
                        break;

                    default : 
                         throw $this->createNotFoundException($content->getType() . ' Not  found');
                }
            }


            // media 
            $medias = $manager->getRepository(Media::class)->findAllByIdCatalog($idCatalog );
            foreach($medias  as $media){
                
                $aEventMedia[$media->getMediaspace()] = $media->getName();
                $path = $media->getFilePath();

                if($media->getName()) $aAllMedia[] = $media->getName() ;
                
            }
            
            $strXmlVideos .= '<idAnnonce>'   . $idCatalog  . '</idAnnonce>' . "\n";
            $strXmlVideos .= '<rubrique>'    . $idCategory  . '</rubrique>' . "\n";
            $strXmlVideos .= '<qrcode>'      . $aEventMedia['qrcode'] . '</qrcode>' . "\n";
            $strXmlVideos .= '<SousTitreVideo></SousTitreVideo>' . "\n";
            $strXmlVideos .= '<jaquetteVideo>'   . $aEventMedia['cover'] . '</jaquetteVideo>' . "\n";
            $strXmlVideos .= '<urlVideo>'   . $aEventMedia['video'] . '</urlVideo>' . "\n";
            $strXmlVideos .= '<logoClient>'   . $aEventMedia['logo'] . '</logoClient>' . "\n";
            $strXmlVideos .= '<infoDescrFR>'    . $infoDescrFR . '</infoDescrFR>' . "\n";
            $strXmlVideos .= '<infoDescrENG>'   . $infoDescrENG . '</infoDescrENG>' . "\n";
            $strXmlVideos .= '<infoDescrESP>'   . $infoDescrESP . '</infoDescrESP>' . "\n";
            $strXmlVideos .= '<infoDescrALL>'   . $infoDescrALL . '</infoDescrALL>' . "\n";
            $strXmlVideos .= '<infoEvennDescrFR>'    . $infoEvennDescrFR . '</infoEvennDescrFR>' . "\n";
            $strXmlVideos .= '<infoEvennDescrENG>'   . $infoEvennDescrENG . '</infoEvennDescrENG>' . "\n";
            $strXmlVideos .= '<infoEvennDescrESP>'   . $infoEvennDescrESP . '</infoEvennDescrESP>' . "\n";
            $strXmlVideos .= '<infoEvennDescrALL>'   . $infoEvennDescrALL . '</infoEvennDescrALL>' . "\n";
            $strXmlVideos .= '<infoEvennIma>'   . $aEventMedia['vis2'] . '</infoEvennIma>' . "\n";
            $strXmlVideos .= '<infoPratiqueDescrFR>'    . $infoPratiqueDescrFR . '</infoPratiqueDescrFR>' . "\n";
            $strXmlVideos .= '<infoPratiqueDescrENG>'   . $infoPratiqueDescrENG . '</infoPratiqueDescrENG>' . "\n";
            $strXmlVideos .= '<infoPratiqueDescrESP>'   . $infoPratiqueDescrESP . '</infoPratiqueDescrESP>' . "\n";
            $strXmlVideos .= '<infoPratiqueDescrALL>'   . $infoPratiqueDescrALL . '</infoPratiqueDescrALL>' . "\n";
            $strXmlVideos .= '<infoPratiqueIma>'   . $aEventMedia['vis1'] . '</infoPratiqueIma>' . "\n";
            $strXmlVideos .= '<TitreVideoFR>'   . $titreVideoFR . '</TitreVideoFR>' . "\n";
            $strXmlVideos .= '<TitreVideoENG>'   . $titreVideoENG . '</TitreVideoENG>' . "\n";
            $strXmlVideos .= '<TitreVideoESP>'   . $titreVideoESP . '</TitreVideoESP>' . "\n";
            $strXmlVideos .= '<TitreVideoALL>'   . $titreVideoALL . '</TitreVideoALL>' . "\n";
            $strXmlVideos .= '<SousTitreVideoFR>'    . $SousTitreVideoFR . '</SousTitreVideoFR>' . "\n";
            $strXmlVideos .= '<SousTitreVideoENG>'   . $SousTitreVideoENG . '</SousTitreVideoENG>' . "\n";
            $strXmlVideos .= '<SousTitreVideoESP>'   . $SousTitreVideoESP . '</SousTitreVideoESP>' . "\n";
            $strXmlVideos .= '<SousTitreVideoALL>'   . $SousTitreVideoALL . '</SousTitreVideoALL>' . "\n";


            $strXmlVideos .= '</videoInfos>' . "\n";
                
            
        }
        $strXmlVideos .= '</videos>' . "\n";

        $strXmlVideos = mb_convert_encoding($strXmlVideos, 'UTF-8');
        $strXmlVideos = chr(239) . chr(187) . chr(191) . $strXmlVideos;
        file_put_contents($workDir . '/videosInfos.xml', $strXmlVideos);
        $strXmlVideos = '';

        // Fichier 93001 (fichier vide)
        file_put_contents($workDir . '/93001', '');

        // CoverFlow.swf 
        $coverFlowSrc ='../public/refs/CoverFlow.swf';
        $coverFlowDst = $workDir . '/CoverFlow.swf';
        if(!copy($coverFlowSrc, $coverFlowDst)){
            throw $this->createNotFoundException('Impossible de copier le fichier CoverFlow.swf');
        }

        // SetNetwork.inf
        $SetNetworkSrc ='../public/refs/SetNetwork.inf';
        $SetNetworkDst = $workDir . '/SetNetwork.inf';
        if(!copy($SetNetworkSrc, $SetNetworkDst)){
            throw $this->createNotFoundException('Impossible de copier le fichier SetNetwork.inf');
        }

        // ActionSync.gui
        $actionSync = '';
        sort($aAllMedia, SORT_NUMERIC);
        foreach($aAllMedia as $media){

            if(!empty($media)) {
                $mediaSrc = '../public/uploads/' . $media;
                $mediaDst = $workDir . '/' . $media;
                if(!copy($mediaSrc, $mediaDst)){
                   dd($mediaSrc . ' to ' . $mediaDst); throw $this->createNotFoundException('Impossible de copier le fichier ' . $media);
                }
            
                $actionSync .=  'M' . $media . "\r\n";
            }
            
	}
        $actionSync .=  'FCoverFlow.swf'  . "\r\n";
        $actionSync .=  'FSetNetwork.inf' . "\r\n";
        $actionSync .=  'H01:30'          . "\r\n";
        $actionSync = mb_convert_encoding($actionSync, 'ISO-8859-1');
        file_put_contents($workDir . '/ActionSync.gui', $actionSync);

        /// FOR TEST ONLY 
        /// ***************************************
       /* $test = 0;
        if ($test == 1 ){
        $actionSyncBm = file('../public/BornSyncTest/ActionSync.gui');
        for($i=0; $i<sizeof($actionSyncBm) ; $i=$i+1)  $actionSyncBm[$i] = substr($actionSyncBm[$i],1);
        sort($actionSyncBm, SORT_NUMERIC);
        //dd($actionSyncBm);
        //for($i=0; $i<sizeof($actionSyncBm) ; $i=$i+1)  $actionSyncBm[$i] = 'M' . $actionSyncBm[$i];
        file_put_contents('../public/BornSyncTest/ActionSync_BM.gui', $actionSyncBm);

        $actionSyncRef = file('../public/BornSync_ref/ActionSync.gui');
        for($i=0; $i<sizeof($actionSyncRef) ; $i=$i+1)  $actionSyncRef[$i] = substr($actionSyncRef[$i],1);
        sort($actionSyncRef, SORT_NUMERIC);
        //for($i=0; $i<sizeof($actionSyncRef) ; $i=$i+1)  $actionSyncRef[$i] = 'M' . $actionSyncRef[$i];
        //dd($actionSyncRef);
        file_put_contents('../public/BornSyncTest/ActionSync_REF.gui', $actionSyncRef);
	}

        /// FOR TEST ONLY 
        /// ***************************************
        /// FOR TEST ONLY 
        /// ***************************************
	*/

        // Bornes.activ et  Bornes.ctrl  
        $release = '';
        $Bornes = $manager->getRepository(Releases::class)->findAll();
        //dd($Bornes);
        foreach($Bornes  as $borne){
            
            $release .= $borne->getRelease() . "\r\n";

        }
        file_put_contents($workDir . '/Bornes.activ', $release);
        file_put_contents($workDir . '/Bornes.ctrl' , $release);


        // ZIP 
        $zip = new  ZipArchive();
        if($zip->open('../public/' . $zipname . '.zip') == TRUE){

            if($zip->open('../public/' . $zipname . '.zip', ZipArchive::CREATE) == TRUE){

                $zip->addEmptyDir('BornSync');

                $options = array('add_path' => 'BornSync/', 'remove_all_path' => TRUE);
                $zip->addGlob($workDir . '/*', GLOB_NOSORT, $options);

                $zip->close(); 
            }
        }



        $this->rem_dir ($workDir);

        $fileName = '../public/' . $zipname . '.zip';
        header("Content-Disposition: attachment; filename=\"". $zipname . '.zip' ."\"");
        header("Content-Length: ".filesize($fileName));
        readfile($fileName);
        
       /* return $this->render('download/index.html.twig', [
            'controller_name' => 'DownloadController',
        ]);*/
    }

    public function uuidv5($namespace, $name) 
	{
		if(!self::is_valid($namespace)) return false;

		// Get hexadecimal components of namespace
		$nhex = str_replace(array('-','{','}'), '', $namespace);

		// Binary Value
		$nstr = '';

		// Convert Namespace UUID to bits
		for($i = 0; $i < strlen($nhex); $i+=2) 
		{
			$nstr .= chr(hexdec($nhex[$i].$nhex[$i+1]));
		}

		// Calculate hash value
		$hash = sha1($nstr . $name);

		return sprintf('%08s-%04s-%04x-%04x-%12s',

		// 32 bits for "time_low"
		substr($hash, 0, 8),

		// 16 bits for "time_mid"
		substr($hash, 8, 4),

		// 16 bits for "time_hi_and_version",
		// four most significant bits holds version number 5
		(hexdec(substr($hash, 12, 4)) & 0x0fff) | 0x5000,

		// 16 bits, 8 bits for "clk_seq_hi_res",
		// 8 bits for "clk_seq_low",
		// two most significant bits holds zero and one for variant DCE1.1
		(hexdec(substr($hash, 16, 4)) & 0x3fff) | 0x8000,

		// 48 bits for "node"
		substr($hash, 20, 12)
		);
	}

	public static function is_valid($uuid) {
		return preg_match('/^\{?[0-9a-f]{8}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?'.
                      '[0-9a-f]{4}\-?[0-9a-f]{12}\}?$/i', $uuid) === 1;
	}

    public function rem_dir ($directory){
        if ($handle = opendir($directory)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    //dd($entry);
                    @unlink($directory . '/' . $entry);
                }
            }
            closedir($handle);
        }

        @rmdir($directory);
    }

    public function toLF ($str){
            $cr = chr(13); 
            $lf = chr(10);
            $str = str_replace("$cr$lf", "$lf", $str); // CRLF(win) -> LF
            $str = str_replace("$cr", "$lf", $str); // CR(mac) -> LF
            return $str;
    }

}
