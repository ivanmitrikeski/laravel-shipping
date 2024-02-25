<?php

namespace Mitrik\Shipping\ServiceProviders\ServicePurolator;

use Exception;
use GuzzleHttp\Client;
use Mitrik\Shipping\ServiceProviders\Address\Address;
use Mitrik\Shipping\ServiceProviders\Box\BoxCollection;
use Mitrik\Shipping\ServiceProviders\Box\BoxInterface;
use Mitrik\Shipping\ServiceProviders\Exceptions\BoxEmpty;
use Mitrik\Shipping\ServiceProviders\Exceptions\BoxOverweight;
use Mitrik\Shipping\ServiceProviders\Exceptions\InvalidCredentials;
use Mitrik\Shipping\ServiceProviders\Exceptions\InvalidOriginPostalCode;
use Mitrik\Shipping\ServiceProviders\Exceptions\InvalidShipmentParameters;
use Mitrik\Shipping\ServiceProviders\Exceptions\PriceNotFound;
use Mitrik\Shipping\ServiceProviders\Exceptions\ShipmentNotCreated;
use Mitrik\Shipping\ServiceProviders\Measurement\Length;
use Mitrik\Shipping\ServiceProviders\Measurement\Weight;
use Mitrik\Shipping\ServiceProviders\ServiceProvider;
use Mitrik\Shipping\ServiceProviders\ServiceProviderRate\ServiceProviderRate;
use Mitrik\Shipping\ServiceProviders\ServiceProviderRate\ServiceProviderRateCollection;
use Mitrik\Shipping\ServiceProviders\ServiceProviderService\ServiceProviderService;
use Mitrik\Shipping\ServiceProviders\ServiceProviderShipment\ServiceProviderShipment;
use Mitrik\Shipping\ServiceProviders\ServiceProviderShipment\ServiceProviderShipmentCollection;
use Mitrik\Shipping\ServiceProviders\ServiceProviderShipment\ServiceProviderShipmentCustomsValue;
use Mitrik\Shipping\ServiceProviders\ShipFrom\ShipFrom;
use Mitrik\Shipping\ServiceProviders\ShipTo\ShipTo;
use SoapClient;
use SoapFault;
use SoapHeader;
use stdClass;

class ServicePurolator extends ServiceProvider
{
    /**
     * Service's name.
     */
    private const NAME = 'Purolator';

    /**
     * @var ServicePurolatorCredentials
     */
    private ServicePurolatorCredentials $credentials;

    /**
     * @param ServicePurolatorCredentials $credentials
     */
    public function __construct(ServicePurolatorCredentials $credentials)
    {
        $this->credentials = $credentials;
    }

    /**
     * @return array
     */
    public static function credentialKeys(): array
    {
        return ServicePurolatorCredentials::credentialKeys();
    }

    /**
     * @return array[]
     */
    public static function serviceCodes(): array
    {
        return [
            'Domestic' => [
                'PurolatorExpress9AM' => 'Purolator Express 9AM',
                'PurolatorExpress10:30AM' => 'Purolator Express 10:30AM ',
                'PurolatorExpress12PM' => 'Purolator Express 12PM',
                'PurolatorExpress' => 'Purolator Express',
                'PurolatorExpressEvening' => 'Purolator Express Evening',
                'PurolatorExpressEnvelope9AM' => 'Purolator Express Envelope 9AM',
                'PurolatorExpressEnvelope10:30AM' => 'Purolator Express Envelope 10:30AM',
                'PurolatorExpressEnvelope12PM' => 'Purolator Express Envelope 12PM',
                'PurolatorExpressEnvelope' => 'Purolator Express Envelope',
                'PurolatorExpressEnvelopeEvening' => 'Purolator Express Envelope Evening',
                'PurolatorExpressPack9AM' => 'Purolator Express Pack 9AM',
                'PurolatorExpressPack10:30AM' => 'Purolator Express Pack 10:30AM',
                'PurolatorExpressPack12PM' => 'Purolator Express Pack 12PM',
                'PurolatorExpressPack' => 'Purolator Express Pack',
                'PurolatorExpressPackEvening' => 'Purolator Express Pack Evening',
                'PurolatorExpressBox9AM' => 'Purolator Express Box 9AM',
                'PurolatorExpressBox10:30AM' => 'Purolator Express Box 10:30AM',
                'PurolatorExpressBox12PM' => 'Purolator Express Box 12PM',
                'PurolatorExpressBox' => 'Purolator Express Box',
                'PurolatorExpressBoxEvening' => 'Purolator Express Box Evening',
                'PurolatorGround' => 'Purolator Ground',
                'PurolatorGround9AM' => 'Purolator Ground 9AM',
                'PurolatorGround10:30AM' => 'Purolator Ground 10:30AM',
                'PurolatorGroundEvening' => 'Purolator Ground Evening',
                'PurolatorQuickShip' => 'Purolator Quick Ship',
                'PurolatorQuickShipEnvelope' => 'Purolator Quick Ship Envelope',
                'PurolatorQuickShipPack' => 'Purolator Quick Ship Pack',
                'PurolatorQuickShipBox' => 'Purolator Quick Ship Box'
            ],
            'International' => [
                'PurolatorExpressU.S.' => 'Purolator Express U.S.',
                'PurolatorExpressU.S.9AM' => 'Purolator Express U.S. 9AM',
                'PurolatorExpressU.S.10:30AM' => 'Purolator Express U.S. 10:30AM',
                'PurolatorExpressU.S.12:00' => 'Purolator Express U.S. 12:00',
                'PurolatorExpressEnvelopeU.S.' => 'Purolator Express Envelope U.S.',
                'PurolatorExpressU.S.Envelope9AM' => 'Purolator Express U.S. Envelope 9AM',
                'PurolatorExpressU.S.Envelope10:30AM' => 'Purolator Express U.S. Envelope 10:30AM',
                'PurolatorExpressU.S.Envelope12:00' => 'Purolator Express U.S. Envelope 12:00',
                'PurolatorExpressPackU.S.' => 'Purolator Express Pack U.S.',
                'PurolatorExpressU.S.Pack9AM' => 'Purolator Express U.S. Pack 9AM',
                'PurolatorExpressU.S.Pack10:30AM' => 'Purolator Express U.S. Pack 10:30AM',
                'PurolatorExpressU.S.Pack12:00' => 'Purolator Express U.S. Pack 12:00',
                'PurolatorExpressBoxU.S.' => 'Purolator Express Box U.S.',
                'PurolatorExpressU.S.Box9AM' => 'Purolator Express U.S. Box 9AM',
                'PurolatorExpressU.S.Box10:30AM' => 'Purolator Express U.S. Box 10:30AM',
                'PurolatorExpressU.S.Box12:00' => 'Purolator Express U.S. Box 12:00',
                'PurolatorGroundU.S.' => 'Purolator Ground U.S.',
                'PurolatorExpressInternational' => 'Purolator Express International',
                'PurolatorExpressInternational9AM' => 'Purolator Express International 9AM',
                'PurolatorExpressInternational10:30AM' => 'Purolator Express International 10:30AM',
                'PurolatorExpressInternational12:00' => 'Purolator Express International 12:00',
                'PurolatorExpressEnvelopeInternational' => 'Purolator Express Envelope International',
                'PurolatorExpressInternationalEnvelope9AM' => 'Purolator Express International Envelope 9AM',
                'PurolatorExpressInternationalEnvelope10:30AM' => 'Purolator Express International Envelope 10:30AM',
                'PurolatorExpressInternationalEnvelope12:00' => 'Purolator Express International Envelope 12:00',
                'PurolatorExpressPackInternational' => 'Purolator Express Pack International',
                'PurolatorExpressInternationalPack9AM' => 'Purolator Express International Pack 9AM',
                'PurolatorExpressInternationalPack10:30AM' => 'Purolator Express International Pack 10:30AM',
                'PurolatorExpressInternationalPack12:00' => 'Purolator Express International Pack 12:00',
                'PurolatorExpressBoxInternational' => 'Purolator Express Box International',
                'PurolatorExpressInternationalBox9AM' => 'Purolator Express International Box 9AM',
                'PurolatorExpressInternationalBox10:30AM' => 'Purolator Express International Box 10:30AM',
                'PurolatorExpressInternationalBox12:00' => 'Purolator Express International Box 12:00'
            ]
        ];
    }

    /**
     * @param Address $addressFrom
     * @param Address $addressTo
     * @param BoxCollection $boxes
     * @return ServiceProviderRateCollection
     * @throws BoxEmpty
     * @throws BoxOverweight
     * @throws InvalidCredentials
     * @throws InvalidOriginPostalCode
     * @throws InvalidShipmentParameters
     * @throws PriceNotFound
     * @throws SoapFault
     */
    public function rates(Address $addressFrom, Address $addressTo, BoxCollection $boxes): ServiceProviderRateCollection
    {
        return $this->rate($addressFrom, $addressTo, $boxes);
    }

    /**
     * @param Address $addressFrom
     * @param Address $addressTo
     * @param BoxCollection $boxes
     * @param ServiceProviderService|null $serviceProviderService
     * @return ServiceProviderRateCollection
     * @throws InvalidCredentials
     * @throws InvalidOriginPostalCode
     * @throws InvalidShipmentParameters
     * @throws PriceNotFound
     * @throws BoxEmpty
     * @throws BoxOverweight
     * @throws SoapFault
     */
    public function rate(Address $addressFrom, Address $addressTo, BoxCollection $boxes, ServiceProviderService|null $serviceProviderService = null): ServiceProviderRateCollection
    {
        $this->checkForEmptyBoxes($boxes);
        $this->checkForOverweightBoxes($boxes);

        $results = new ServiceProviderRateCollection();

        $location = $this->credentials->test() ? 'https://devwebservices.purolator.com/EWS/V2/Estimating/EstimatingService.asmx' : 'https://webservices.purolator.com/EWS/V2/Estimating/EstimatingService.asmx';

        $client = new SoapClient( $location . '?wsdl', [
            'trace' => true,
            'location' => $location,
            'uri' => "http://purolator.com/pws/datatypes/v2",
            'login' => $this->credentials->key(),
            'password' => $this->credentials->password()
        ]);

        // Define the SOAP Envelope Headers
        $headers[] = new SoapHeader('http://purolator.com/pws/datatypes/v2', 'RequestContext', [
            'Version' => '2.0',
            'Language' => 'en',
            'GroupID' => 'xxx',
            'RequestReference' => 'Rating',
            'UserToken' => $this->credentials->userToken()
        ]);

        // Apply the SOAP Header to your client
        $client->__setSoapHeaders($headers);


        $request = new stdClass();
        $request->Shipment = new stdClass();
        $request->Shipment->SenderInformation = new stdClass();
        $request->Shipment->SenderInformation->Address = new stdClass();

        // Populate the Origin Information
        $request->Shipment->SenderInformation->Address->Name = $addressFrom->fullName();
        $request->Shipment->SenderInformation->Address->StreetName = $addressFrom->line1();
        $request->Shipment->SenderInformation->Address->City = $addressFrom->city();
        $request->Shipment->SenderInformation->Address->Province = $addressFrom->stateCodeIso2();
        $request->Shipment->SenderInformation->Address->Country = $addressFrom->countryCodeIso2();
        $request->Shipment->SenderInformation->Address->PostalCode = $addressFrom->postalCode();
//        $request->Shipment->SenderInformation->Address->PhoneNumber->CountryCode = "1";
//        $request->Shipment->SenderInformation->Address->PhoneNumber->AreaCode = "905";
//        $request->Shipment->SenderInformation->Address->PhoneNumber->Phone = "5555555";

        // Populate the Destination Information
        $request->Shipment->ReceiverInformation = new stdClass();
        $request->Shipment->ReceiverInformation->Address = new stdClass();
        $request->Shipment->ReceiverInformation->Address->Name = $addressTo->fullName();
//        $request->Shipment->ReceiverInformation->Address->StreetNumber = "2245";
        $request->Shipment->ReceiverInformation->Address->StreetName = $addressTo->line1();
        $request->Shipment->ReceiverInformation->Address->City = $addressTo->city();
        $request->Shipment->ReceiverInformation->Address->Province = $addressTo->stateCodeIso2();
        $request->Shipment->ReceiverInformation->Address->Country = $addressTo->countryCodeIso2();
        $request->Shipment->ReceiverInformation->Address->PostalCode = $addressTo->postalCode();
//        $request->Shipment->ReceiverInformation->Address->PhoneNumber->CountryCode = "1";
//        $request->Shipment->ReceiverInformation->Address->PhoneNumber->AreaCode = "604";
//        $request->Shipment->ReceiverInformation->Address->PhoneNumber->Phone = "2982181";

        // Future Dated Shipments - YYYY-MM-DD format
        $request->Shipment->ShipmentDate = date('Y-m-d');

        // Populate the Package Information
        $request->Shipment->PackageInformation = new stdClass();
        $request->Shipment->PackageInformation->TotalWeight = new stdClass();
        $request->Shipment->PackageInformation->TotalWeight->Value = $boxes->weight();
        $request->Shipment->PackageInformation->TotalWeight->WeightUnit = $boxes->unitOfMeasurementWeight() === Weight::KG ? 'kg' : 'lb';
        $request->Shipment->PackageInformation->TotalPieces = $boxes->count();
        $request->Shipment->PackageInformation->ServiceID = $addressTo->countryCodeIso2() === 'CA' ? 'PurolatorExpress' : ($addressTo->countryCodeIso2() == 'US' ? 'PurolatorExpressU.S.' : 'PurolatorExpressInternational');

        $request->Shipment->PackageInformation->PiecesInformation = new stdClass();
        $request->Shipment->PackageInformation->PiecesInformation->Piece = new stdClass();

        $request->Shipment->PackageInformation->PiecesInformation = new stdClass();
        $request->Shipment->PackageInformation->PiecesInformation->Piece = [];

        $boxIndex = 0;
        /** @var BoxInterface $box */
        foreach ($boxes as $box) {
            $request->Shipment->PackageInformation->PiecesInformation->Piece[$boxIndex] = new stdClass();

            $request->Shipment->PackageInformation->PiecesInformation->Piece[$boxIndex]->Weight = new stdClass();
            $request->Shipment->PackageInformation->PiecesInformation->Piece[$boxIndex]->Weight->Value = $box->weight();
            $request->Shipment->PackageInformation->PiecesInformation->Piece[$boxIndex]->Weight->WeightUnit = $box->unitOfMeasurementWeight() === Weight::KG ? 'kg' : 'lb';

            $request->Shipment->PackageInformation->PiecesInformation->Piece[$boxIndex]->Length = new stdClass();
            $request->Shipment->PackageInformation->PiecesInformation->Piece[$boxIndex]->Length->Value = $box->length();
            $request->Shipment->PackageInformation->PiecesInformation->Piece[$boxIndex]->Length->DimensionUnit = $box->unitOfMeasurementSize() === Length::CM ? 'cm' : "in";

            $request->Shipment->PackageInformation->PiecesInformation->Piece[$boxIndex]->Width = new stdClass();
            $request->Shipment->PackageInformation->PiecesInformation->Piece[$boxIndex]->Width->Value = $box->width();
            $request->Shipment->PackageInformation->PiecesInformation->Piece[$boxIndex]->Width->DimensionUnit = $box->unitOfMeasurementSize() === Length::CM ? 'cm' : "in";

            $request->Shipment->PackageInformation->PiecesInformation->Piece[$boxIndex]->Height = new stdClass();
            $request->Shipment->PackageInformation->PiecesInformation->Piece[$boxIndex]->Height->Value = $box->height();
            $request->Shipment->PackageInformation->PiecesInformation->Piece[$boxIndex]->Height->DimensionUnit = $box->unitOfMeasurementSize() === Length::CM ? 'cm' : "in";

            // Implement if needed
//            $request->Shipment->PackageInformation->PiecesInformation->Piece[$boxIndex]->Options->OptionIDValuePair[0]->ID="SpecialHandling";
//            $request->Shipment->PackageInformation->PiecesInformation->Piece[$boxIndex]->Options->OptionIDValuePair[0]->Value="true";
//            $request->Shipment->PackageInformation->PiecesInformation->Piece[$boxIndex]->Options->OptionIDValuePair[1]->ID="SpecialHandlingType";
//            $request->Shipment->PackageInformation->PiecesInformation->Piece[$boxIndex]->Options->OptionIDValuePair[1]->Value="LargePackage";
        }

        // Populate the Payment Information
        $request->Shipment->PaymentInformation = new stdClass();
        $request->Shipment->PaymentInformation->PaymentType = "Sender";
        $request->Shipment->PaymentInformation->BillingAccountNumber = $this->credentials->billingAccount();
        $request->Shipment->PaymentInformation->RegisteredAccountNumber = $this->credentials->registeredAccount();

        // Populate the Pickup Information
        $request->Shipment->PickupInformation = new stdClass();
        $request->Shipment->PickupInformation->PickupType = "DropOff";
        $request->ShowAlternativeServicesIndicator = "true";

        // Define OptionsInformation
        // ResidentialSignatureDomestic
        // $request->Shipment->PackageInformation->OptionsInformation->Options->OptionIDValuePair->ID = "ResidentialSignatureDomestic";
        // $request->Shipment->PackageInformation->OptionsInformation->Options->OptionIDValuePair->Value = "true";

        // ResidentialSignatureIntl
        // $request->Shipment->PackageInformation->OptionsInformation->Options->OptionIDValuePair->ID = "ResidentialSignatureIntl";
        // $request->Shipment->PackageInformation->OptionsInformation->Options->OptionIDValuePair->Value = "true";

        // Implement if needed
        // OriginSignatureNotRequired
//        $request->Shipment->PackageInformation->OptionsInformation = new \stdClass();
//        $request->Shipment->PackageInformation->OptionsInformation->Options = new \stdClass();
//
//        $request->Shipment->PackageInformation->OptionsInformation->Options->OptionIDValuePair = [];
//        $request->Shipment->PackageInformation->OptionsInformation->Options->OptionIDValuePair[0] = new \stdClass();
//        $request->Shipment->PackageInformation->OptionsInformation->Options->OptionIDValuePair[0]->ID = "OriginSignatureNotRequired";
//        $request->Shipment->PackageInformation->OptionsInformation->Options->OptionIDValuePair[0]->Value = "true";

        // Execute the request and capture the response
        try {
            $response = $client->GetFullEstimate($request);

            if (isset($response->ShipmentEstimates->ShipmentEstimate)) {
                foreach ($response->ShipmentEstimates->ShipmentEstimate as $priceQuote) {
                    if ($serviceProviderService !== null) {
                        if ($serviceProviderService->serviceCode() !== $priceQuote->ServiceID) {
                            continue;
                        }
                    }

                    $serviceProviderServiceItem = new ServiceProviderService($priceQuote->ServiceID, self::serviceCodes()['Domestic'][$priceQuote->ServiceID] ?? self::serviceCodes()['International'][$priceQuote->ServiceID] ?? $priceQuote->ServiceID);
                    $serviceProviderRate = new ServiceProviderRate($serviceProviderServiceItem, $priceQuote->TotalPrice, (array) $priceQuote);
                    $results->addServicePrice($serviceProviderRate);
                }

                return $results;
            }
        } catch (Exception $e) {
            if ($e->getMessage() === 'Unauthorized') {
                throw new InvalidCredentials($e->getMessage());
            }

            throw $e;
        }

        if (isset($response->ResponseInformation->Errors->Error)) {
            $code = (int) (is_array($response->ResponseInformation->Errors->Error) ? $response->ResponseInformation->Errors->Error[0]->Code : $response->ResponseInformation->Errors->Error->Code) ?? 0;
            $description = (is_array($response->ResponseInformation->Errors->Error) ? $response->ResponseInformation->Errors->Error[0]->Description : $response->ResponseInformation->Errors->Error->Description) ?? 'Invalid Request.';

            throw match ($code) {
                3001149 => new InvalidOriginPostalCode($description, $code),
                1000000, 1100509, 1100512 => new InvalidShipmentParameters($description, $code),
                default => new Exception($description, $code),
            };
        }

        throw new PriceNotFound('Price not found.');
    }

    public function ship(ShipFrom $shipFrom, ShipTo $shipTo, BoxCollection $boxes, ServiceProviderService $serviceProviderService, ServiceProviderShipmentCustomsValue|null $serviceProviderShipmentCustomsValue = null, $customData = []): ServiceProviderShipmentCollection
    {
        $this->checkForEmptyBoxes($boxes);
        $this->checkForOverweightBoxes($boxes);

        $client = $this->createShipmentClient();

        $request = new stdClass();
        $request->Shipment = new stdClass();
        $request->Shipment->SenderInformation = new stdClass();
        $request->Shipment->SenderInformation->Address = new stdClass();

        // Populate the Origin Information
        $request->Shipment->SenderInformation->Address->Name = $shipFrom->name();
        $request->Shipment->SenderInformation->Address->Company = $shipFrom->company();
        $request->Shipment->SenderInformation->Address->StreetName = $shipFrom->address()->line1();
        $request->Shipment->SenderInformation->Address->City = $shipFrom->address()->city();
        $request->Shipment->SenderInformation->Address->Province = $shipFrom->address()->stateCodeIso2();
        $request->Shipment->SenderInformation->Address->Country = $shipFrom->address()->countryCodeIso2();
        $request->Shipment->SenderInformation->Address->PostalCode = $shipFrom->address()->postalCode();
        $request->Shipment->SenderInformation->Address->PhoneNumber = new stdClass();
        $request->Shipment->SenderInformation->Address->PhoneNumber->CountryCode = $shipFrom->phone()->countryCode();
        $request->Shipment->SenderInformation->Address->PhoneNumber->AreaCode = $shipFrom->phone()->areaCode();
        $request->Shipment->SenderInformation->Address->PhoneNumber->Phone = $shipFrom->phone()->number();

        // Populate the Destination Information
        $request->Shipment->ReceiverInformation = new stdClass();
        $request->Shipment->ReceiverInformation->Address = new stdClass();
        $request->Shipment->ReceiverInformation->Address->Name = $shipTo->name();
        $request->Shipment->ReceiverInformation->Address->Company = $shipTo->company();
//        $request->Shipment->ReceiverInformation->Address->StreetNumber = "2245";
        $request->Shipment->ReceiverInformation->Address->StreetName = $shipTo->address()->line1();
        $request->Shipment->ReceiverInformation->Address->City = $shipTo->address()->city();
        $request->Shipment->ReceiverInformation->Address->Province = $shipTo->address()->stateCodeIso2();
        $request->Shipment->ReceiverInformation->Address->Country = $shipTo->address()->countryCodeIso2();
        $request->Shipment->ReceiverInformation->Address->PostalCode = $shipTo->address()->postalCode();
        $request->Shipment->ReceiverInformation->Address->PhoneNumber = new stdClass();
        $request->Shipment->ReceiverInformation->Address->PhoneNumber->CountryCode = $shipTo->phone()->countryCode();
        $request->Shipment->ReceiverInformation->Address->PhoneNumber->AreaCode = $shipTo->phone()->areaCode();
        $request->Shipment->ReceiverInformation->Address->PhoneNumber->Phone = $shipTo->phone()->number();

        // Future Dated Shipments - YYYY-MM-DD format
        $request->Shipment->ShipmentDate = $shipFrom->shipDate()->format('Y-m-d');

        // Populate the Package Information
        $request->Shipment->PackageInformation = new stdClass();
        $request->Shipment->PackageInformation->TotalWeight = new stdClass();
        $request->Shipment->PackageInformation->TotalWeight->Value = $boxes->weight();
        $request->Shipment->PackageInformation->TotalWeight->WeightUnit = $boxes->unitOfMeasurementWeight() === Weight::KG ? 'kg' : 'lb';
        $request->Shipment->PackageInformation->TotalPieces = $boxes->count();
        $request->Shipment->PackageInformation->ServiceID = $serviceProviderService->serviceCode();

        $request->Shipment->PackageInformation->PiecesInformation = new stdClass();
        $request->Shipment->PackageInformation->PiecesInformation->Piece = new stdClass();

        $request->Shipment->PackageInformation->PiecesInformation = new stdClass();
        $request->Shipment->PackageInformation->PiecesInformation->Piece = [];

        // Populate the Payment Information
        $request->Shipment->PaymentInformation = new stdClass();
        $request->Shipment->PaymentInformation->PaymentType = "Sender";
        $request->Shipment->PaymentInformation->BillingAccountNumber = $this->credentials->billingAccount();
        $request->Shipment->PaymentInformation->RegisteredAccountNumber = $this->credentials->registeredAccount();

        // Populate the Pickup Information
        $request->Shipment->PickupInformation = new stdClass();
        $request->Shipment->PickupInformation->PickupType = "DropOff";
        $request->ShowAlternativeServicesIndicator = "true";

        $results = new ServiceProviderShipmentCollection();

        /** @var BoxInterface $box */
        foreach ($boxes as $box) {
            $request->Shipment->PackageInformation->PiecesInformation = new stdClass();
            $request->Shipment->PackageInformation->PiecesInformation->Piece = [];

            $request->Shipment->PackageInformation->PiecesInformation->Piece[0] = new stdClass();

            $request->Shipment->PackageInformation->PiecesInformation->Piece[0]->Weight = new stdClass();
            $request->Shipment->PackageInformation->PiecesInformation->Piece[0]->Weight->Value = $box->weight();
            $request->Shipment->PackageInformation->PiecesInformation->Piece[0]->Weight->WeightUnit = $box->unitOfMeasurementWeight() === Weight::KG ? 'kg' : 'lb';

            $request->Shipment->PackageInformation->PiecesInformation->Piece[0]->Length = new stdClass();
            $request->Shipment->PackageInformation->PiecesInformation->Piece[0]->Length->Value = $box->length();
            $request->Shipment->PackageInformation->PiecesInformation->Piece[0]->Length->DimensionUnit = $box->unitOfMeasurementSize() === Length::CM ? 'cm' : "in";

            $request->Shipment->PackageInformation->PiecesInformation->Piece[0]->Width = new stdClass();
            $request->Shipment->PackageInformation->PiecesInformation->Piece[0]->Width->Value = $box->width();
            $request->Shipment->PackageInformation->PiecesInformation->Piece[0]->Width->DimensionUnit = $box->unitOfMeasurementSize() === Length::CM ? 'cm' : "in";

            $request->Shipment->PackageInformation->PiecesInformation->Piece[0]->Height = new stdClass();
            $request->Shipment->PackageInformation->PiecesInformation->Piece[0]->Height->Value = $box->height();
            $request->Shipment->PackageInformation->PiecesInformation->Piece[0]->Height->DimensionUnit = $box->unitOfMeasurementSize() === Length::CM ? 'cm' : "in";

            // Implement if needed
//            $request->Shipment->PackageInformation->PiecesInformation->Piece[$boxIndex]->Options->OptionIDValuePair[0]->ID="SpecialHandling";
//            $request->Shipment->PackageInformation->PiecesInformation->Piece[$boxIndex]->Options->OptionIDValuePair[0]->Value="true";
//            $request->Shipment->PackageInformation->PiecesInformation->Piece[$boxIndex]->Options->OptionIDValuePair[1]->ID="SpecialHandlingType";
//            $request->Shipment->PackageInformation->PiecesInformation->Piece[$boxIndex]->Options->OptionIDValuePair[1]->Value="LargePackage";


            $request = (object) array_merge_recursive($customData, (array) $request);


            // Execute the request and capture the response
            try {
                $response = $client->CreateShipment($request);

                if (isset($response->PiecePINs->PIN->Value)) {
                    $requestLabel = new stdClass();
                    $requestLabel->DocumentCriterium = new stdClass();
                    $requestLabel->DocumentCriterium->DocumentCriteria = new stdClass();
                    $requestLabel->DocumentCriterium->DocumentCriteria->PIN = new stdClass();
                    $requestLabel->DocumentCriterium->DocumentCriteria->PIN->Value = $response->PiecePINs->PIN->Value;

                    $requestLabel->DocumentCriterium->DocumentCriteria->DocumentTypes = new stdClass();
                    $requestLabel->DocumentCriterium->DocumentCriteria->DocumentTypes->DocumentType = "DomesticBillOfLading";

                    //OutputType - Valid values are PDF, ZPL, DPL
                    $requestLabel->OutputType = "PDF";

                    //Specify Synchronous as true, if you want to consume the data object in response
                    $requestLabel->Synchronous = false;
                    $requestLabel->SynchronousSpecified = true;

                    //Execute the request and capture the response
                    $clientLabel = $this->createDocumentsClient();

                    $responseLabel = $clientLabel->GetDocuments($requestLabel);

                    if (isset($responseLabel->Documents->Document->DocumentDetails->DocumentDetail->URL)) {
                        $clientLabelDownload = new Client();

                        $responseData = $clientLabelDownload->get($responseLabel->Documents->Document->DocumentDetails->DocumentDetail->URL);

                        $shippingLabelData = base64_encode($responseData->getBody()->getContents());

                        $results->push(new ServiceProviderShipment($response->PiecePINs->PIN->Value, $shippingLabelData, 'PDF', (array) $response));
                    }

                }
            } catch (Exception $e) {
                if ($e->getMessage() === 'Unauthorized') {
                    throw new InvalidCredentials($e->getMessage());
                }

                throw $e;
            }

        }

        if (isset($response->ResponseInformation->Errors->Error)) {
            $code = (int) (is_array($response->ResponseInformation->Errors->Error) ? $response->ResponseInformation->Errors->Error[0]->Code : $response->ResponseInformation->Errors->Error->Code) ?? 0;
            $description = (is_array($response->ResponseInformation->Errors->Error) ? $response->ResponseInformation->Errors->Error[0]->Description : $response->ResponseInformation->Errors->Error->Description) ?? 'Invalid Request.';

            throw match ($code) {
                3001149 => new InvalidOriginPostalCode($description, $code),
                1000000, 1100509, 1100512 => new InvalidShipmentParameters($description, $code),
                default => new Exception($description, $code),
            };
        }

        if ($results->isNotEmpty()) {
            return $results;
        }

        throw new ShipmentNotCreated('Unable to create shipment.');
    }

    private function createShipmentClient(): SoapClient
    {
        $location = $this->credentials->test() ? 'https://devwebservices.purolator.com/EWS/V2/Shipping/ShippingService.asmx' : 'https://webservices.purolator.com/EWS/V2/Shipping/ShippingService.asmx';

        $client = new SoapClient( $location . '?wsdl', [
            'trace' => true,
            'location' => $location,
            'uri' => "http://purolator.com/pws/datatypes/v2",
            'login' => $this->credentials->key(),
            'password' => $this->credentials->password()
        ]);

        // Define the SOAP Envelope Headers
        $headers[] = new SoapHeader('http://purolator.com/pws/datatypes/v2', 'RequestContext', [
            'Version' => '2.0',
            'Language' => 'en',
            'GroupID' => 'xxx',
            'RequestReference' => 'Rating',
            'UserToken' => $this->credentials->userToken()
        ]);

        // Apply the SOAP Header to your client
        $client->__setSoapHeaders($headers);

        return $client;
    }

    private function createDocumentsClient(): SoapClient
    {
        $location = $this->credentials->test() ? 'https://devwebservices.purolator.com/PWS/V1/ShippingDocuments/ShippingDocumentsService.asmx' : 'https://webservices.purolator.com/PWS/V1/ShippingDocuments/ShippingDocumentsService.asmx';

        $client = new SoapClient( $location . '?wsdl', [
            'trace' => true,
            'location' => $location,
            'uri' => "http://purolator.com/pws/datatypes/v1",
            'login' => $this->credentials->key(),
            'password' => $this->credentials->password()
        ]);

        // Define the SOAP Envelope Headers
        $headers[] = new SoapHeader('http://purolator.com/pws/datatypes/v1', 'RequestContext', [
            'Version'           =>  '1.3',
            'Language'          =>  'en',
            'GroupID'           =>  'xxx',
            'RequestReference'  =>  'Example Code',
            'UserToken' => $this->credentials->userToken()
        ]);

        // Apply the SOAP Header to your client
        $client->__setSoapHeaders($headers);

        return $client;
    }
}
