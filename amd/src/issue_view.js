
define(['jquery'], function($) {
    class IssueView {
        constructor(closeissuestring, commentandcloseissuestring) {
            const commentandcommitbtn = $("button[name=closeissue]").get(0);
            const commentEditor = $("#commenteditor").get(0);
            commentEditor.addEventListener("change", event => {
                if (commentEditor.value == ('<p dir="ltr" style="text-align: left;"><br></p>')) {
                    commentandcommitbtn.innerText = closeissuestring;
                } else {
                    commentandcommitbtn.innerText = commentandcloseissuestring;
                }
            })
        }
    }

    return IssueView;

})
