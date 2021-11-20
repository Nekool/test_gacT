<?php

namespace App\Controller;

use App\Entity\Csv;
use App\Form\CsvType;
use App\Form\TraitementDureType;
use App\Form\TraitementTopTenType;
use App\Form\TraitementTotalSmsType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    //INDEX présente la page d'index
    /**
     * @Route("/", name="index" ,methods={"GET"})
     */
    public function index(Request $request): Response
    {
        $form = $this->createForm(CsvType::class, null);

        return $this->render('index_templates/main.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    //TRAITE l'input de fichier

    /**
     * @Route("/", name="traitement_fichier" ,methods={"POST"})
     */
    public function postIndex(Request $request): Response
    {

        $form = $this->createForm(CsvType::class, null, ["method" => "POST"]);
        $form->handleRequest($request);
        /** @var UploadedFile $csvFile */
        $csvFile = $form->get('csv_file')->getData();
        $new_file = $csvFile->move("./upload/", $csvFile->getClientOriginalName());
        //CHECK SI LE DEPLACEMENT S'EST BIEN PASSE ET QUE LE FICHIER EST UN CSV
        if (is_a($new_file, 'Symfony\Component\HttpFoundation\File\File') && $new_file->getExtension() == "csv") {
            $csv = new Csv();
            $result = $csv->loadDataFromCsv($new_file->getRealPath(), $this->getDoctrine());
            if ($result == "succes") {
                //si success  -> redirige vers la page de visualisation de commandes
                return $this->redirectToRoute('page_post_traitement');
            } else {
                //sinon redirige avec le message d'erreur
                return $this->render('index_templates/main.html.twig', [
                    'form' => $form->createView(),
                    "errorMessage" => $result
                ]);
            }
        } else {
            return $this->render('index_templates/main.html.twig', [
                'form' => $form->createView(),
                "errorMessage" => "Le chargement du fichier à échoué"
            ]);
        }
    }
    /**
     * @Route("/traitement", name="page_post_traitement" ,methods={"GET"})
     */
    public function vuePostTraitement(Request $request): Response
    {
        //FORMULAIRE RECHERCHE PAR DATE
        $result = $request->get('result');
        $formFindByDate = $this->createForm(TraitementDureType::class, null, [
            'action' => '/traitement/FindByDate',
            'method' => 'POST'
        ]);
        //FORMULAIRE RECHERCHE TOP TEN
        $formFindTopTen = $this->createForm(TraitementTopTenType::class, null, [
            'action' => '/traitement/FindTopTen',
            'method' => 'POST'
        ]);
        //FORMULAIRE RECHERCHE TOTAL SMS / UTILISATEUR
        $formFindTotalSms = $this->createForm(TraitementTotalSmsType::class, null, [
            'action' => '/traitement/FindTotalSms',
            'method' => 'POST'
        ]);

        return $this->render('traitement_templates/main.html.twig', [
            'formFindByDate' => $formFindByDate->createView(),
            'formFindTopTen' => $formFindTopTen->createView(),
            'formFindTotalSms' => $formFindTotalSms->createView(),
            'result'=>$result
        ]);
    }
    /**
     * @Route("/traitement/FindByDate", name="find_by_date" ,methods={"POST"})
     */
    public function traitementFindByDate(Request $request): Response
    {
        $date = $request->request->get('traitement_dure')['date'];
        $date = implode("-",$date);
        $result = Csv::FindByDate($this->getDoctrine(),$date)[0]["time"];
        if($result == null){//si =null => aucun appel apres la date séléctionnée
            $result = "00:00:00";
        }
        return $this->redirectToRoute('page_post_traitement',["result"=>
            ["data"=>$result, "origin"=>"FindByDate","date"=>$date]
        ]);

    }
    /**
     * @Route("/traitement/FindTopTen", name="find_top_ten" ,methods={"POST"})
     */
    public function traitementFindTopTen(Request $request): Response
    {
        $result = Csv::FindTopTen($this->getDoctrine());
        if(!is_array($result)){//si =null => aucun appel apres la date séléctionnée
            $result = "no results";
        }
        return $this->redirectToRoute('page_post_traitement',["result"=>
            ["data"=>$result, "origin"=>"FindTopTen"]
        ]);

    }
    /**
     * @Route("/traitement/FindTotalSms", name="find_total_sms" ,methods={"POST"})
     */
    public function traitementFindTotalSms(Request $request): Response
    {
        $result = Csv::FindTotalSms($this->getDoctrine());
        if(!is_array($result)){//si =null => aucun appel apres la date séléctionnée
            $result = "no results";
        }
        return $this->redirectToRoute('page_post_traitement',["result"=>
            ["data"=>$result, "origin"=>"FindTotalSms"]
        ]);

    }
}
