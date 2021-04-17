{
    "info":
    {
        "title": "String parsing 2",
        "purpose": "Check the quotes escaping",
        "created": "2018-05-25",
        "author": "Syscall"
    },
    "rules":
    {
        "final_trim": true
    },
    "expected_results":
    {
        "schema": [
            {
                "qty": 1,
                "optional": 0,
                "type": "TYPE_STRING",
                "data": ["\"This is \\\"Another String\\\"!\""]
            }
        ]
    }
}