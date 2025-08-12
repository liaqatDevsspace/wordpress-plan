class Bookstore_Payment_Gateway extends WC_Payment_Gateway{
    public function __construct(){
        this->id = 'bookstore_gateway';
    }
}