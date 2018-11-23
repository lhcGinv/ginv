{{-- demo.index --}}
@section("account.list")
    select *
        from accounts
    where true

    @if ($account_type===0)
        and account_type = 0
    @endif

    @if ($account_type)
        and account_type=:$account_type
    @endif

    @if ($in_account_id)
        and account_id in (:in_account_id)
    @endif

    @if($limit)
        limit :limit
    @endif

    @if($offset)
            offset :offset
    @endif
@endsection


@section("account.count")
    select count(*)
        from accounts
    where
        true

    @if ($account_type===0)
        and account_type = 0
    @endif

    @if ($account_type)
        and account_type=:$account_type
    @endif

    @if ($in_account_id)
        and account_id in (:in_account_id)
    @endif
    limit 1
@endsection


