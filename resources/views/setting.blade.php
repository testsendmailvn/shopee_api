<div class="card my-2">
    <div class="card-header">
        <div class="row">            
            <div class="col-12">
                <a class="btn btn-danger" href="{{ isset($url) ? $url : '' }}">Kết nối với sàn Shopee</a>                               
            </div>
        </div>
    </div>
    <div class="card-body text-center">
        <div class="row">
            <div class="col-3">
                <label>Partner ID:</label>
                <input type="text" class="form-control" id="partner_id"
                    value="{{ isset($partner_id) ? $partner_id : env('APP_SHOPEE_PARTNER_ID') }}">
            </div>
            <div class="col-3">
                <label>Partner Key:</label>
                <input type="password" class="form-control" id="partner_key"
                    value="{{ isset($partner_key) ? $partner_key : env('APP_SHOPEE_PARTNER_KEY') }}">
            </div>
            <div class="col-3">
                <label>Shop ID:</label>
                <input type="text" class="form-control" id="shop_id" value="{{isset($shop_id) ? $shop_id : ''}}">
            </div>
            <div class="col-3">
                <label>Tên shop:</label>
                <input type="text" class="form-control" id="shop_name" value="{{isset($shop_name) ? $shop_name : ''}}">
            </div>
        </div>
        <div class="row">
            <div class="col-3">
                <label>Access Token:
                    <a href="javascript:void(0)" onclick="getToken()">
                        <i class="fa fa-refresh"></i>
                    </a>
                </label>
                <input type="text" class="form-control" id="access_token" value="{{isset($access_token) ? $access_token : ''}}">
            </div>
            <div class="col-3">
                <label>Refesh Token:</label>
                <input type="text" class="form-control" id="refresh_token" value="{{isset($refresh_token) ? $refresh_token : ''}}">
            </div>
            <div class="col-3">
                <label>Expire time:</label>
                <input type="text" class="form-control" id="expire_in" value="{{isset($expired_time) ? $expired_time : ''}}">
            </div>
           
        </div>
    </div>
</div>
<div class="card">
    <div class="card-header">
        DANH SÁCH SHOP TRÊN SÀN
    </div>
    <div class="card-body">
        <div class="row mx-4">                        
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="textcenter" width="8%">STT</th>
                        <th width="13%">Mã Shop</th>
                        <th width="20%">Tên shop</th>
                        <th width="15.5%">Trạng thái</th>
                        <th>Xử lý</th>
                    </tr>
                </thead>
                <tbody>
                    @if (isset($model))
                        @foreach ($model as $key => $item)
                        <tr>
                            <td>{{$key + 1}}</td>
                            <td>{{isset($item['shop_id']) ? $item['shop_id'] : '' }}</td>
                            <td>{{isset($item['shop_name']) ? $item['shop_name'] : '' }}</td>
                            <td>{{isset($item['is_active']) && $item['is_active'] == 1 ? 'Kích hoạt' : 'Không kích hoạt' }}</td>
                            <td>
                                <i class="fa fa-edit fa-lg text-primary"></i>
                                <i class="fa fa-trash fa-lg text-danger"></i>
                            </td>
                        </tr>                   
                        @endforeach
                    @endif
                    
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
   

function getToken() {    
const queryString = window.location.search;
const urlParams = new URLSearchParams(queryString);    
var data = {
    code: urlParams.get('code'),
    shop_id: $('#shop_id').val(),
    partner_id: $('#partner_id').val(),
    partner_key: $('#partner_key').val(),
}
$.ajax({
    url: `/token`,
    type: 'POST',
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
    },
    data: data,
    success: function(res) {
        if (res.code === true) {
            $('#access_token').val(res.data.accessToken)
            $('#refresh_token').val(res.data.newRefreshToken)
            $('#expire_in').val(res.data.expired_time)
            $('#shop_name').val(res.data.shop_name)
            $.notify(res.message, "success");
            location.reload();
        } else {
            $.notify(res.message, "error");
        }
    },
    error: function(err) {
        $.notify(err.message, "success");
    }
});
}
</script>