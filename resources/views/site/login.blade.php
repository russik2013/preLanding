<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/js/bootstrap.min.js"></script>
<script src="//code.jquery.com/jquery-1.11.1.min.js"></script>
<!------ Include the above in your HEAD tag ---------->


<style>

    .error_div {
        padding: 5px 5px 5px 5px ;
    }

    .myForm {
        min-width: 500px;
        position: absolute;
        text-align: center;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 2.5rem
    }
    @media (max-width: 500px) {
        .myForm {
            min-width: 90%;
        }
    }

</style>
<div class="container myForm">
    <div class="row">
        <div class="col-md-6 col-md-offset-3">
            <div class="panel panel-login">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <form id="login-form" action="{{route('admin.auth')}}" method="post" role="form" style="display: block;">


                                @if($errors->any())
                                    <div class="form-group bg-danger error_div">
                                        <h4>{{$errors->first()}}</h4>
                                    </div>
                                @endif

                                <div class="form-group">
                                    <input type="text" name="email" id="username" tabindex="1" class="form-control" placeholder="User email" value="">
                                </div>

                                {!!  csrf_field() !!}

                                <div class="form-group">
                                    <input type="password" name="password" id="password" tabindex="2" class="form-control" placeholder="Password">
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-sm-6 col-sm-offset-3">
                                            <input type="submit" name="login-submit" id="login-submit" tabindex="4" class="form-control btn btn-login" value="Log In">
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>