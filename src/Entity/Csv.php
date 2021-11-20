<?php

namespace App\Entity;

use App\Repository\CsvRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CsvRepository::class)
 */
class Csv
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $compte_facture;

    /**
     * @ORM\Column(type="integer")
     */
    private $numero_facture;

    /**
     * @ORM\Column(type="integer")
     */
    private $numero_abonne;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(type="time")
     */
    private $heure;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $dure_volume_reel;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $dure_volume_facture;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompteFacture(): ?int
    {
        return $this->compte_facture;
    }

    public function setCompteFacture(int $compte_facture): self
    {
        $this->compte_facture = $compte_facture;

        return $this;
    }

    public function getNumeroFacture(): ?int
    {
        return $this->numero_facture;
    }

    public function setNumeroFacture(int $numero_facture): self
    {
        $this->numero_facture = $numero_facture;

        return $this;
    }

    public function getNumeroAbonne(): ?int
    {
        return $this->numero_abonne;
    }

    public function setNumeroAbonne(int $numero_abonne): self
    {
        $this->numero_abonne = $numero_abonne;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getHeure(): ?\DateTimeInterface
    {
        return $this->heure;
    }

    public function setHeure(\DateTimeInterface $heure): self
    {
        $this->heure = $heure;

        return $this;
    }

    public function getDureVolumeReel(): ?string
    {
        return $this->dure_volume_reel;
    }

    public function setDureVolumeReel(?string $dure_volume_reel): self
    {
        $this->dure_volume_reel = $dure_volume_reel;

        return $this;
    }

    public function getDureVolumeFacture(): ?string
    {
        return $this->dure_volume_facture;
    }

    public function setDureVolumeFacture(?string $dure_volume_facture): self
    {
        $this->dure_volume_facture = $dure_volume_facture;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }
    public static function clear_table($em){
        $query = "DELETE FROM csv";
        $em->getManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();

    }
    public function loadDataFromCsv($file,$em): string{
        if (file_exists($file)){
            self::clear_table($em);
            $query = "LOAD DATA LOCAL INFILE '".addslashes($file)."' INTO TABLE csv 
            CHARACTER SET latin1
            fields terminated by ';'
            lines terminated by '\n'
            IGNORE 3 LINES
            (@compte_facture,@numero_facture,@numero_abonne,@date,@heure,@dure_volume_reel,@dure_volume_facture,@type) 
            set compte_facture=@compte_facture,numero_facture=@numero_facture,numero_abonne=@numero_abonne,
            `date`=DATE_FORMAT(STR_TO_DATE(@date,'%d/%m/%Y'),'%Y-%m-%d'),heure=@heure,dure_volume_reel=@dure_volume_reel,dure_volume_facture=@dure_volume_facture,type=@type;";
            $em->getManager();
            $stmt = $em->getConnection()->prepare($query);
            try {
                $stmt->execute();
                return "succes";
            }catch (\Exception $e ){var_dump($query);
                return $e->getMessage();
            }
        }else{
            return "File not found";
        }
    }

    public static function FindByDate($em,$date){//recherche la somme de temps d'appel effectué apres une certaine date
        $query = "SELECT 
                    SEC_TO_TIME(
                        SUM(case when type LIKE '%appel%' and `date`>= DATE_FORMAT(STR_TO_DATE('".$date."','%d-%m-%Y'),'%Y-%m-%d') then TIME_TO_SEC(dure_volume_reel) end)
                        )as time FROM `csv`";

        $em->getManager();
        $stmt = $em->getConnection()->prepare($query);
        $result = $stmt->execute();

        return $result->fetchAll();
    }
    public static function FindTopTen($em){

        $query = "SELECT DISTINCT numero_abonne ,dure_volume_reel,numero_facture FROM `csv` where type LIKE '%connexion%' AND '18:00' > heure AND heure > '08:00' ORDER by dure_volume_reel DESC LIMIT 10";
        $em->getManager();
        $stmt = $em->getConnection()->prepare($query);
        $result = $stmt->execute();
        return $result->fetchAll();
    }
    public static function FindTotalSms($em){
        $query = "SELECT numero_abonne, count(*) as compte FROM `csv` WHERE type LIKE '%sms%' GROUP BY numero_abonne";
        $em->getManager();
        $stmt = $em->getConnection()->prepare($query);
        $result = $stmt->execute();
        return $result->fetchAll();
    }
}