function comment(start,end,videoName,comment, color) {
    this.startTime=start;
    this.endTime=end;
    this.name = videoName;
    this.comment = comment;
    this.color = color;
}

function fullcomment(commID, source_id, authID, parentID, textcont, start, end,
		commdate, deleted, tempcommentbool, hasvideobool, videofilename,
		authorname, authorjoindate, authorrole, color) {
	this.id = commID;
	this.sourceId = source_id;
	this.author = authID;
	this.parentId = parentID;
	this.text = textcont;
	this.startTime = start;
	this.endTime = end;
	this.date = commdate;
	this.isDeleted = deleted;
	this.isTemporalComment = tempcommentbool;
	this.hasVideo = hasvideobool;
	this.videoFileName = videofilename;
	this.authorName = authorname;
	this.authorJoinDate = authorjoindate;
	this.authorRole = authorrole;
	this.color = color;
}