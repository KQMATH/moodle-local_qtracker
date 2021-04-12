


class IssueView {
    constructor() {
        const commentandcommitbtn = $("button[name=closeissue]");
        const comment = $("#commenteditoreditable").children("p");
        comment.onchange(event => {
            console.log('comment has changed')
            if (event.target.value == "") {
                commentandcommitbtn.text = closeissuestring;
            } else {
                commentandcommitbtn.text = commentandcloseissuestring;
            }
        })
    }

}
