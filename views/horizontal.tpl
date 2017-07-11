<div class="ws-schedule table-responsive" id="ws-schedule{{scheduleID}}">
    <table class="table table-hover table-bordered">
        <thead>
            <tr class="topheader">
                <th class="rowheader"></th>
                {{#timeinterval}}<th class="rowheader">{{.}}</th>{{/timeinterval}}
            </tr>
        </thead>
        <tbody>
            {{#days}}
            <tr class="row1 firstrowofday">
                {{^hidden}}
                <th rowspan="{{rowspan}}" class="rowheader">{{dayname}}</th>
                {{/hidden}}
                {{#items}}
                    {{#iscontent}}
                    <td class="ws-item-{{itemID}} cat-{{catID}}" style="{{style}}" tooltip="{{tooltip}}" colspan="{{nbintervals}}" data-hasqtip="{{hasqtip}}">
                        <div class="ws-item-title">
                            {{#url}}<a target="{{target}}" href="{{url}}">{{item}}</a>{{/url}}
                            {{^url}}{{item}}{{/url}}
                            <p>{{start}} - {{end}}<br>duration : {{duration}}</p>
                        </div>
                    </td>
                    {{/iscontent}}
                    {{^iscontent}}
                    <td></td>
                    {{/iscontent}}
                {{/items}}
            </tr>
            {{/days}}
        </tbody>
    </table>
</div>