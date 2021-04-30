<?php

/**
 *  @var   [统一错误码 必须严格执行]
 */
namespace Raichu\Provider;

class Ecode
{
    #正常返回
    const OK = 1;
    #api返回
    const APIOK = 0;
    #请求错误
    const BadRequest = 400;
    #未认证
    const Unauthorized = 401;
    #无权限新增角色组
    const Forbidden = 403;
    #找不到这个页面
    const NotFound = 404;
    #服务器错误
    const ApiCallError = 500;
    #请求错误2
    const ServerError = 10000;
    #返回码错误
    const CodeError = 10003;
    #数据格式无效
    const DataInvalid = 10004;
    #没有找到数据
    const DataNotFound = 10005;
    #数据冲突
    const DataConflict = 10006;
    #参数缺失
    const LackParams = 10007;
    #参数类型错误
    const ParamsTypeError = 10008;
    #参数格式错误
    const ParamsFormatError = 10009;

}
