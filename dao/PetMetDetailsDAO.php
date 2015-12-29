<?php

require_once 'BaseDAO.php';
class PetMetDetailsDAO
{
    
    private $con;
    private $msg;
    private $data;
    
    // Attempts to initialize the database connection using the supplied info.
    public function PetMetDetailsDAO() {
        $baseDAO = new BaseDAO();
        $this->con = $baseDAO->getConnection();
    }
    
    public function saveDetail($petMetDetail) {
        try {
            if(move_uploaded_file($petMetDetail->getImageTemporaryName(), $petMetDetail->getTargetPathOfImage())) {
                $sql = "INSERT INTO petmet(image_path, pet_category, pet_breed, pet_age, pet_gender, pet_description, post_date, email)
                        VALUES 
                        ('".$petMetDetail->getTargetPathOfImage()."',
                         '".$petMetDetail->getCategoryOfPet()."',
                         '".$petMetDetail->getBreedOfPet()."',
                         '".$petMetDetail->getAgeOfPet()."',
                         '".$petMetDetail->getGenderOfPet()."',
                         '".$petMetDetail->getDescriptionOfPet()."',
						  '".$petMetDetail->getPostDate()."',
						  '".$petMetDetail->getEmail()."'
						)";
        
                $isInserted = mysqli_query($this->con, $sql);
                if ($isInserted) {
                    $this->data = "PET_DETAILS_SAVED";
                } else {
                    $this->data = "ERROR";
                }
            } else {
                $this->data = "ERROR_UPLOAD_FILE";
            }
        } catch(Exception $e) {
            echo 'SQL Exception: ' .$e->getMessage();
        }
        return $this->data;
    }
    
    public function showDetail($pageWiseData) {
		
		$sqlAddress="SELECT latitude,longitude FROM userDetails WHERE email='".$pageWiseData->getEmail()."' ";
		$latlong = mysqli_query($this->con, $sqlAddress);
		
		$latLongValue = mysqli_fetch_row($latlong);
		$latitude = $latLongValue[0];
		$longitude = $latLongValue[1];
        $sql = "SELECT pm.image_path, pm.pet_category, pm.pet_breed,pm.pet_age,pm.pet_gender,pm.pet_description,pm.post_date,ud.name,ud.email,ud.mobileno,( 3959 * acos( cos( radians('$latitude') ) * cos( radians( ud.latitude ) ) * cos( radians( ud.longitude ) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians( ud.latitude ) ) ) ) * 1.609344 AS distance
                FROM petmet pm
                INNER JOIN userDetails ud
                ON pm.email = ud.email
                HAVING distance < 5 ORDER BY distance";
        
        try {
            $result = mysqli_query($this->con, $sql);
            $numOfRows = mysqli_num_rows($result);
            
            $rowsPerPage = 10;
            $totalPages = ceil($numOfRows / $rowsPerPage);
            
            $this->con->options(MYSQLI_OPT_CONNECT_TIMEOUT, 500);
            
            if (is_numeric($pageWiseData->getCurrentPage())) {
                $currentPage = (int) $pageWiseData->getCurrentPage();
            }
            
            if ($currentPage >= 1 && $currentPag <= $totalPages) {
                $offset = ($currentPage - 1) * $rowsPerPage;
            
                $sql = "SELECT pm.image_path, pm.pet_category, pm.pet_breed,pm.pet_age,pm.pet_gender,pm.pet_description,pm.post_date,ud.name,ud.email,ud.mobileno,( 3959 * acos( cos( radians('$latitude') ) * cos( radians( ud.latitude ) ) * cos( radians( ud.longitude ) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians( ud.latitude ) ) ) ) * 1.609344 AS distance
						FROM petmet pm
						INNER JOIN userDetails ud
						ON pm.email = ud.email
						HAVING distance < 5 ORDER BY distance, post_date DESC LIMIT $offset, $rowsPerPage";
                $result = mysqli_query($this->con, $sql);
                
                $this->data=array();
                while ($rowdata = mysqli_fetch_assoc($result)) {
                    $this->data[]=$rowdata;
                }
            }
            
            
        } catch(Exception $e) {
            echo 'SQL Exception: ' .$e->getMessage();
        }
        return $this->data;
    }
	 public function showRefreshListDetail($DateOfPost) {
        $sqlAddress="SELECT latitude,longitude FROM userDetails WHERE email='".$DateOfPost->getEmail()."' ";
		$latlong = mysqli_query($this->con, $sqlAddress);
		
		$latLongValue = mysqli_fetch_row($latlong);
		$latitude = $latLongValue[0];
		$longitude = $latLongValue[1];
        
        try {
            $sql = "SELECT pm.image_path, pm.pet_category, pm.pet_breed,pm.pet_age,pm.pet_gender,pm.pet_description,pm.post_date,ud.name,ud.email,ud.mobileno,( 3959 * acos( cos( radians('$latitude') ) * cos( radians( ud.latitude ) ) * cos( radians( ud.longitude ) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians( ud.latitude ) ) ) ) * 1.609344 AS distance
						FROM petmet pm
						INNER JOIN userDetails ud
						ON pm.email = ud.email WHERE post_date > '".$DateOfPost->getPostDate()."' ";
            $result = mysqli_query($this->con, $sql);   
            $this->data=array();
            while ($rowdata = mysqli_fetch_assoc($result)) {
                $this->data[]=$rowdata;
            }            
        } catch(Exception $e) {
            echo 'SQL Exception: ' .$e->getMessage();
        }
        return $this->data;
    }
}
?>