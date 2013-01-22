function signlink(startTime,endTime,link) {
	 this.startTime    = startTime;
	 this.endTime      = endTime;
	 this.link         = link;
}

function comment(start,end,videoName,comment) {
	this.startTime = start;
	this.endTime   = end;
	this.name      = videoName;
	this.comment   = comment;
}

function fullComment(ident, authorid, parentid, start, end, createddate, deleted, textcontent, temporal, hasvid, videofilename, color) {
    this.id             = ident;
    this.authorID       = authorid;
    this.parentID       = parentid;
    this.startTime      = start;
    this.endTime        = end;
    this.created        = createddate;
    this.isdeleted      = deleted;
    this.textContent    = textcontent;
    this.isTemporal     = temporal;
    this.hasVideo       = hasvid;
    this.videoFileName  = videofileName;
    this.color		= color;
}
