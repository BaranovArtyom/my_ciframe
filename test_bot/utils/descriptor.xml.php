<ServerApplication xmlns="https://online.moysklad.ru/xml/ns/appstore/app/v2"
             xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
             xsi:schemaLocation="https://online.moysklad.ru/xml/ns/appstore/app/v2 https://online.moysklad.ru/xml/ns/appstore/app/v2/application-v2.xsd">
    <iframe>
        <sourceUrl><?=cfg()->appBaseUrl?>/iframe.php</sourceUrl>
    </iframe>
    <vendorApi>
        <endpointBase><?=cfg()->appBaseUrl?>/vendor-endpoint.php</endpointBase>
    </vendorApi>
    <access>
        <resource><?=cfg()->moyskladJsonApiEndpointUrl?></resource>
        <scope>admin</scope>
    </access>
    <widgets>
        <entity.counterparty.view>
            <sourceUrl><?=cfg()->appBaseUrl?>/widgets/counterparty-widget.php</sourceUrl>
            <height>
                <fixed>250px</fixed>
            </height>
            <supports>
                <open-feedback/>
            </supports>
        </entity.counterparty.view>
        <document.customerorder.edit>
            <sourceUrl><?=cfg()->appBaseUrl?>/widgets/customerorder-widget.php</sourceUrl>
            <height>
                <fixed>250px</fixed>
            </height>
            <supports>
                <open-feedback/>
            </supports>
        </document.customerorder.edit>
        <document.demand.edit>
            <sourceUrl><?=cfg()->appBaseUrl?>/widgets/demand-widget.php</sourceUrl>
            <height>
                <fixed>250px</fixed>
            </height>
            <supports>
                <open-feedback/>
            </supports>
        </document.demand.edit>
    </widgets>
</ServerApplication>